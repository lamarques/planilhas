<section class="content-header">
    <h1>Cadastro</h1>
    <ol class="breadcrumb">
        <li><a href="index.php">Início</a></li>
        <li><a href="javascript:Load('cadastro/funcionarios', 'conteudo');">Funcionários</a></li>
        <li class="active">cadastro</li>
    </ol>
</section>

<section class="content">
    <form id="cadastros.funcionarios" name="cadastros.funcionarios" method="post" onsubmit="
            Padrao.Executa(
    {literal}{{/literal}
                validar: true,
                form: 'cadastros.funcionarios',
                botao: 'salvar',
                pagina: 'cadastro/acao.php',
                parametros: PegaDados.Formulario('cadastros.funcionarios', true) + '&acao=cadastrafuncionario',
                loadPagina: 'cadastro/funcionarios.php',
                loadDiv: 'conteudo'
    {literal}}{/literal}); return false;">
        {if $funcionario.id_funcionarios}
            <input type="hidden" id="id_funcionarios" name="id_funcionarios" value="{$funcionario.id_funcionarios}">
        {/if}
        <div class="form-group">
            <label for="matricula">Matricula:</label>
            <input type="text" id="matricula" name="matricula" value="{$funcionario.matricula}" class="form-control" valida="sim,,Matrícula">
        </div>
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="{$funcionario.nome}" class="form-control" valida="sim,,Nome">
        </div>
        <div class="form-group">
            <label for="siglanomemeio">Sigla Nome do meio:</label>
            <input type="text" id="siglanomemeio" name="siglanomemeio" value="{$funcionario.siglanomemeio}"
                   class="form-control" valida="nao,,Sigla">
        </div>
        <div class="form-group">
            <label for="sobrenome">Sobrenome:</label>
            <input type="text" id="sobrenome" name="sobrenome" value="{$funcionario.sobrenome}" class="form-control" valida="sim,,Sobrenome">
        </div>
        <div class="form-group">
            <label for="usuario">Usuário:</label>
            <input type="text" id="usuario" name="usuario" value="{$funcionario.usuario}" class="form-control" valida="sim,,Usuário">
        </div>
        <div>
            <label for="senha">Senha:</label>
        </div>
        <div class="form-group input-group">
            <input type="password" id="senha" name="senha" aria-describedby="olho" value="" class="form-control" {if $funcionario.id_funcionario}valida="nao,,Senha"{else} valida="sim,,Senha"{/if}>
            <span class="input-group-btn">
                <button id="olho" class="btn btn-default" type="button">
                    <i class="glyphicon glyphicon-eye-open"></i>
                </button>
            </span>
        </div>
        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="text" id="email" name="email" value="{$funcionario.email}" class="form-control" valida="sim,,E-mail">
        </div>
        <div class="form-group">
            <label for="permissao">Permissão:</label>
            <input class="form-inline" type="checkbox" id="permissao" name="permissao[]" value="1000"
                   {if $funcionario.permissao eq 1000}checked{/if} valida="nao,,Permissão"> Administrador
            <input class="form-inline" type="checkbox" id="permissao" name="permissao[]" value="999999"
                   {if $funcionario.permissao eq 999999}checked{/if} valida="nao,,Permissão"> Root
            <input class="form-inline" type="checkbox" id="permissao" name="permissao[]" value="1"
                   {if $funcionario.permissao eq 1}checked{/if} valida="nao,,Permissão"> Usuário
        </div>
        <div class="form-group">
            <label for="ativo">Ativo:</label>
            <input class="form-inline" type="checkbox" id="ativo" name="ativo[]" value="t"
                   {if $funcionario.ativo eq 1}checked{/if}> Ativo
        </div>
        <div class="form-group">
            <button type="submit" id="salvar" class="btn btn-default">
                Salvar
            </button>
            <button type="button" id="cancelar" class="btn btn-default"
                    onclick="Load('cadastro/funcionarios.php', 'conteudo');">
                Cancelar
            </button>
        </div>
    </form>
</section>

<script type="text/javascript">
    {literal}
    var senha = $('#senha');
    var olho = $("#olho");

    olho.mousedown(function () {
        senha.attr("type", "text");
    });

    olho.mouseup(function () {
        senha.attr("type", "password");
    });
    // para evitar o problema de arrastar a imagem e a senha continuar exposta,
    //citada pelo nosso amigo nos comentários
    $("#olho").mouseout(function () {
        $("#senha").attr("type", "password");
    });

    $('#nome, #sobrenome').keyup(function () {
       var nome = $('#nome').val().toLowerCase();
       var sobrenome = $('#sobrenome').val().toLowerCase();

        $('#usuario').val(nome + '.' + sobrenome);
    });
    {/literal}
</script>
