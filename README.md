<p align="center">
  <a href="http://nestjs.com/" target="blank"><img src="http://maratona.fullcycle.com.br/public/img/logo-maratona.png"/></a>
</p>

<p>Git Original: <a href="https://github.com/codeedu/laravel-microservice-quickstart" target="blank">https://github.com/codeedu/laravel-microservice-quickstart</a></p>

## Descrição

Microsserviço de catálogo

## Rodar a aplicação

#### Crie os containers com Docker

```bash
$ docker-compose up
```

#### Acesse no browser

```
http://localhost:8000
```

## Projetos


### Terminando upload do model vídeo
#### Branch: Proj07-TerminandoVideo
<b>OBS:</b> Desafio não realizado.

Nesta fase, você deverá acrescentar mais campos de upload na tabela e no model Vídeo. Já temos <b>video_file</b> e <b>thumb_file</b>.

Agora teremos:

banner_file
trailer_file
Você deve criar também os testes de validação de tamanho máximo para os 4 campos. Abaixo está o tamanho máximo permitido:

video_file - 50GB
thumb_file - 5MB
banner_file - 10MB
trailer_file - 1GB

Agora com todos estes arquivos em mãos, consolide os testes de upload no teste de integração do model Vídeo. Precisamos saber se no próprio model Video, os uploads estão funcionando. Você pode criar 4 testes: <b>testCreateWithBasicFields</b> e <b>testUpdateWithBasicFields</b> para testar somente a criação ou atualização do vídeo sem upload e <b>testCreateWithFiles</b>  e <b>testUpdateWithFiles</b> para focar somente no upload.


<b>Desafio (Opcional):</b> Na trait de uploads, crie um método que receba o nome de um arquivo e devolva o endereço correto do arquivo, ou seja, o endereço WEB de acesso ao arquivo. Este método servirá como base para gerar qualquer endereço de qualquer arquivo do vídeo.

Você deve criar o teste deste método e criar mutators do Eloquent para permitir que os endereços sejam acessíveis como campos, exemplo: <b>$video->thumb_file_url</b> ou <b>$video->video_file_url</b>.

Teste tudo isso.

Boa sorte!

### Primeiro upload de arquivos
#### Branch: Proj06-UploadArquivos

### Implementando recurso de vídeo e relacionamentos
#### Branch: Proj05-VideoeRelacionamentos

### Projeto Abstract CRUD e Resource CastMember
#### Branch: Proj04-AbstractCRUDeCastMember

### Projeto Testes com HTTP
#### Branch: Proj03-TestesComHTTP

### Projeto Testes de integração em categorias e gêneros
#### Branch: Proj02-TestesIntegracaoCategGeneros

### Projeto Criando recurso Category
#### Branch: Proj01-CriandoRecursoCategory
