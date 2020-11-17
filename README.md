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


### Implementando API Resource
#### Branch: Proj08-APIResource
[Notas do Aluno]
Não consegui resolver todos os erros dos testes dos controllers e no BasicCrudController após a implementação do API Resource. Faltou muito conhecimento meu dentro do Laravel quanto a implementação disto no Video =(
Acabei precisando assistir a revisão do Projeto para aprender a fazer.



Nesta fase, você deve implementar o recurso API Resource nos controllers e testa-los.

Crie os resources para: Category, CastMember, Genre e Video.

No resource de Genre, você deve incluir na serialização, as categorias relacionadas.

No resource de Video, você deve incluir na serialização, as categorias e gêneros relacionados e as urls dos arquivos.

Aplique todos os resources nos controllers e faça os testes em todos os métodos do CRUD, exceto no destroy. Lembre-se de testar sempre a estrutura do JSON, com o método jsonStructure e também usando o método assertResource.


<b>Desafio (Opcional):</b> Agora com a mudança para o API Resource, o controller básico de CRUD foi modificado, será necessário testa-lo também.

Aplique os testes em todos os métodos, exceto no destroy. Lembre-se que neste controller não temos resposta HTTP, logo em cada retorno de cada ação do controller, teremos a instância do Resource para avaliar.

Somente avalie se os dados do resource são iguais ao toArray do model CategoryStub.

Boa sorte!

### Terminando upload do model vídeo
#### Branch: Proj07-TerminandoVideo

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
