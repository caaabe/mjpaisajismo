"""Pruebas HTTP contra una base MySQL/MariaDB descartable.
MJ_TEST_DSN, MJ_TEST_USER, MJ_TEST_PASSWORD apuntan EXCLUSIVAMENTE a una base vacía.
Ejecutar: python3 tests/integration.py. No usa la configuración de producción.
"""
import os, re, json, subprocess, tempfile, time, urllib.request, urllib.parse, urllib.error, http.cookiejar
from pathlib import Path
ROOT = Path(__file__).resolve().parents[1]
php = os.environ.get('PHP_BIN', 'php')
dsn = os.environ.get('MJ_TEST_DSN')
if not dsn:
    raise SystemExit('Falta MJ_TEST_DSN: usá una base MySQL/MariaDB vacía y descartable.')

def php_string(value):
    return "'" + value.replace('\\', '\\\\').replace("'", "\\'") + "'"

def check(condition, message):
    if not condition: raise AssertionError(message)
    print('OK:', message)

with tempfile.TemporaryDirectory(prefix='mj-tests-') as directory:
    config = Path(directory)/'config.php'
    config.write_text('<?php return '+ '[' + ','.join(php_string(k)+'=>'+php_string(v) for k,v in {
        'dsn':dsn,'user':os.environ.get('MJ_TEST_USER','root'), 'password':os.environ.get('MJ_TEST_PASSWORD',''),
        'base_url':'http://127.0.0.1:18080', 'mail_from':'no-reply@example.test'
    }.items()) + '];')
    env = dict(os.environ, MJ_CONFIG=str(config))
    def cli(code):
        result = subprocess.run([php,'-r', "require 'app/bootstrap.php';"+code], cwd=ROOT,env=env,text=True,capture_output=True,check=True)
        return result.stdout
    # Rechazar la ejecución sobre una base que ya contiene datos.
    check(cli("echo count(db()->query('SHOW TABLES')->fetchAll());") == '0', 'Base de prueba vacía')
    subprocess.run([php,'bin/setup.php','admin@example.test'], cwd=ROOT,env=env,input='Jardines-seguros-2026\n',text=True,check=True)
    logs = open(Path(directory)/'server.log','w+')
    server = subprocess.Popen([php,'-d','upload_max_filesize=10M','-d','post_max_size=110M','-S','127.0.0.1:18080','-t',str(ROOT)],cwd=ROOT,env=env,stdout=logs,stderr=logs)
    generated_paths=[]
    try:
        jar=http.cookiejar.CookieJar(); browser=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
        base='http://127.0.0.1:18080'
        def request(path, data=None, multipart=None):
            headers={}
            if multipart:
                boundary='mjIntegrationBoundary'; body=bytearray()
                for key,value in data.items():
                    body.extend(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode())
                filename,payload,mime=multipart
                body.extend(f'--{boundary}\r\nContent-Disposition: form-data; name="photos[]"; filename="{filename}"\r\nContent-Type: {mime}\r\n\r\n'.encode());body.extend(payload);body.extend(f'\r\n--{boundary}--\r\n'.encode())
                headers['Content-Type']='multipart/form-data; boundary='+boundary; encoded=bytes(body)
            else: encoded=urllib.parse.urlencode(data).encode() if data is not None else None
            try:
                response=browser.open(urllib.request.Request(base+path,data=encoded,headers=headers))
                return response.status,response.read().decode(),response.url
            except urllib.error.HTTPError as error: return error.code,error.read().decode(),error.url
        def csrf(html): return re.search(r'name="csrf" value="([a-f0-9]+)"',html).group(1)
        for _ in range(50):
            try: status,html,url=request('/admin/');break
            except urllib.error.URLError:time.sleep(.1)
        check(url.endswith('/admin/login.php'), 'El administrador exige sesión')
        status,html,_=request('/admin/login.php',{'csrf':'invalid','email':'admin@example.test','password':'bad'})
        check(status==403,'Se rechaza CSRF inválido')
        _,html,_=request('/admin/login.php')
        _,html,url=request('/admin/login.php',{'csrf':csrf(html),'email':'admin@example.test','password':'Jardines-seguros-2026'})
        check(url.endswith('/admin/'),'Login correcto')
        _,html,_=request('/admin/project.php')
        form={'csrf':csrf(html),'title':'Prueba <script>alert(1)</script>','description':'Descripción del jardín','location':'Montevideo','year':'2026','position':'1','action':'save'}
        _,html,_=request('/admin/project.php',form,('mal.php',b'<?php echo "mal";','image/jpeg'))
        check('Usá fotos JPG' in html and cli("echo query('SELECT COUNT(*) FROM projects')->fetchColumn();")=='0','Archivo falso rechazado sin guardar proyecto')
        _,html,_=request('/admin/project.php')
        form['csrf']=csrf(html)
        _,html,url=request('/admin/project.php',form,('foto.jpeg',(ROOT/'assets/jardin-01.jpeg').read_bytes(),'image/jpeg'))
        check('Borrador guardado' in html,'Crear borrador con foto')
        project_id=int(urllib.parse.parse_qs(urllib.parse.urlparse(url).query)['id'][0]); project_path=f'/admin/project.php?id={project_id}'
        _,public,_=request('/')
        check('Prueba &lt;script&gt;' not in public,'El borrador no aparece públicamente')
        photo=json.loads(cli(f"echo json_encode(project_photos({project_id}));"))[0]
        generated_paths.append(ROOT/photo['path'])
        check(re.fullmatch(r'uploads/projects/[a-f0-9]{32}\.jpg',photo['path']) is not None,'Nombre de foto aleatorio y extensión segura')
        dimensions=json.loads(cli("echo json_encode(getimagesize(ROOT . '/"+photo['path']+"'));"))
        check(max(dimensions['0'],dimensions['1']) <= 1920 and dimensions['mime']=='image/jpeg', 'Foto normalizada a JPG dentro del límite de dimensiones')
        form.update({'csrf':csrf(html),'published':'1','cover':str(photo['id']),f"order[{photo['id']}]":'0',f"alt[{photo['id']}]":'Jardín con flores'})
        _,html,_=request(project_path,form)
        check('Proyecto publicado' in html,'Publicación de proyecto')
        _,public,_=request('/')
        check('Prueba &lt;script&gt;' in public and '<script>alert(1)</script>' not in public,'Proyecto público con texto escapado')
        check('\\u003Cscript\\u003E' in public,'Datos JSON protegidos contra cierre de script')
        form['csrf']=csrf(html);form[f"remove[{photo['id']}]"]=str(photo['id'])
        _,html,_=request(project_path,form)
        check('Agregá al menos una foto' in html and (ROOT/photo['path']).exists(),'No se puede publicar sin fotos; rollback conserva el archivo')
        form.pop(f"remove[{photo['id']}]");form.pop('published');form['csrf']=csrf(html)
        _,html,_=request(project_path,form)
        _,public,_=request('/')
        check('Prueba &lt;script&gt;' not in public,'Ocultar retira el proyecto de la web')
        status,_,_=request(project_path,{'action':'delete','csrf':'bad'})
        check(status==403 and cli('echo query("SELECT COUNT(*) FROM projects")->fetchColumn();')=='1','Eliminación protegida por CSRF')
        _,html,_=request(project_path)
        _,html,_=request(project_path,{'action':'delete','csrf':csrf(html)})
        check(cli('echo query("SELECT COUNT(*) FROM projects")->fetchColumn();')=='0' and not (ROOT/photo['path']).exists(),'Eliminar borra proyecto y foto subida')
        # Recuperación: token guardado solo como hash, de un único uso.
        token='a'*64
        cli("query('INSERT INTO password_resets (admin_id, token_hash, expires_at) VALUES (1,?,?)',[hash('sha256','"+token+"'),time()+1800]);")
        _,html,_=request('/admin/password.php?token='+token)
        _,html,url=request('/admin/password.php',{'csrf':csrf(html),'token':token,'password':'Nueva-clave-segura-2026','confirmation':'Nueva-clave-segura-2026'})
        check('Contraseña actualizada' in html and url.endswith('/admin/login.php'),'Restablecimiento de contraseña')
        _,_,url=request('/admin/')
        check(url.endswith('/admin/login.php'),'El cambio de contraseña invalida la sesión')
        _,html,_=request('/admin/password.php?token='+token)
        _,html,_=request('/admin/password.php',{'csrf':csrf(html),'token':token,'password':'Otra-clave-segura-2026','confirmation':'Otra-clave-segura-2026'})
        check('El enlace venció o ya se utilizó' in html,'El token no se puede reutilizar')
        for _ in range(11):
            _,html,_=request('/admin/login.php')
            _,html,_=request('/admin/login.php',{'csrf':csrf(html),'email':'admin@example.test','password':'incorrecta'})
        check('Demasiados intentos' in html,'Límite de intentos de acceso')
        print('Todas las pruebas HTTP pasaron. La base de prueba puede descartarse.')
    finally:
        server.terminate();server.wait(timeout=10);logs.close()
        for path in generated_paths:
            path.unlink(missing_ok=True)
