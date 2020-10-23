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

### Primeiro upload de arquivos
#### Branch: Proj06-UploadArquivos

Nesta fase você deve criar o campo para upload do vídeo na tabela vídeos:

video_file, string e nullable

#### Validação

Os uploads não serão obrigatórios ao se enviar um POST ou PUT para /videos, logo nas regras de validação não teremos a regra required

Devemos validar o upload de vídeo requerendo somente o tipo video/mp4 e um tamanho máximo (especifique um valor simbolico para o tamanho). Pesquise na documentação do Laravel como validar tipos de arquivo e o tamanho máximo de um arquivo.

Crie o teste de validação do upload de vídeo, é necessário testar a invalidação do tipo do vídeo e o tamanho máximo.

#### Upload

Implemente o upload do vídeo (somente com POST) como foi mostrado no capítulo e aplique um teste para verificar se o arquivo foi criado corretamente após o término do cadastro.

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
