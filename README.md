# php_portalnoticias
Sistema de Portal de Noticias em PHP

Requisitos
 
Servidor Linux com cPanel  (cPanel.net), Apache, Php 5.4 a 5.5, Banco MySQL, phpMyAdmin 

Acesse o cPanel - o gerenciador de Banco de dados MySQL, crie o Banco de Dados MySQL, o Usuário de acesso ao banco + senha, depois atribua todas as permissões do usuário ao Banco. 

Abra o phpMyAdmin, selecione o banco que criou e importe a base de dados (BANCO-DE-DADOS.sql) que está dentro da pasta /INSTALACAO  

Arquivo de Conexão com o Banco 

Na pasta /Portal, acesse: 

-------  
/conexao/db.php 
Edite as informações do banco de dados, usuário e senha para conexão com o banco entre as linhas 4 a 7 depois salve e feche. 

$servidor = 'localhost'; //Geralmente por padrão é localhost, mas dependendo da hospedagem pode ser outro endereço. 
$usuario = 'USUARIOCPANEL_USUARIOBANCO'; 
$senha = 'SENHA'; 
$banco = 'USUARIOCPANEL_BANCO';  

-------  
/conexao/events.php 
Neste arquivo você vai editar a linha 11 com os mesmos dados de acesso ao banco, altere só o que está em vermelho. 
$bdd = new PDO('mysql:host=localhost;dbname=USUARIOCPANEL_BANCO', 'USUARIOCPANEL_USUARIOBANCO ', 'SENHA');  

-------  
No arquivo /index.php  

Na linha 4 do arquivo index.php você deve alterar o nome da pasta de instalação do script, o padrão é a pasta agenda, altere para o nome da pasta que quiser instalar o script.  

define("raiz","/portal/"); // insira aqui a pasta de instalação do script 

Agora compacte o conteúdo da pasta /Portal  em um arquivo .zip e faça o upload deste arquivo zipado pelo gerenciador de arquivos do cPanel e assim que finalizar o upload  descompacte o .zip lá pelo gerenciador mesmo. 

E pronto! 

Acesso
www.seusite.com/portal
