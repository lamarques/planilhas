<section class="content-header">
    <h1>Listagem Funcionários</h1>
    <ol class="breadcrumb">
        <li><a href="index.php">Início</a></li>
        <li class="active">Funcionários</li>
    </ol>
</section>
<section class="content">
    <div class="box-header">
        <button class="btn btn-default" onclick="Load('cadastro/cadastro_funcionario.php', 'conteudo');">Adicionar</button>
    </div>
    <div class="box-body">
        <table id="listagemFuncionarios" class="table table-bordered table-striped dataTable" role="grid">
            <thead>
            <tr>
                <th>
                    Matricula
                </th>
                <th>
                    Nome Completo
                </th>
                <th>
                    Usuário
                </th>
                <th>
                    E-mail
                </th>
                <th>
                    Administrador
                </th>
                <th>
                    Ações
                </th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$funcionarios item='funcionario'}
                <tr>
                    <td>
                        {$funcionario.matricula}
                    </td>
                    <td>
                        {$funcionario.nome} {$funcionario.siglanomemeio} {$funcionario.sobrenome}
                    </td>
                    <td>
                        {$funcionario.usuario}
                    </td>
                    <td>
                        {$funcionario.email}
                    </td>
                    <td>
                        {if $funcionario.permissao eq 999999}
                            <b>ROOT</b>
                        {elseif $funcionario.permissao eq 1000}
                            Sim
                        {else}
                            Não
                        {/if}
                    </td>
                    <td>
                        {if $sessao_funcionario.permissao eq 1000 or $sessao_funcionario.permissao eq 999999 }
                            <button onclick="javascript: Load('cadastro/cadastro_funcionario.php?id_funcionarios={$funcionario.id_funcionarios}', 'conteudo');">
                                Editar
                            </button>
                            <button id="{$funcionario.id_funcionarios}"
                                    onclick="ExcluirPadrao(this, 'cadastros.funcionarios', '{$funcionario.id_funcionarios}', 'cadastro/cadastro_funcionarios.php?id_funcionarios={$funcionario.id_funcionarios}', 'conteudo');">
                                Apagar
                            </button>
                            <span id="Aguardando{$funcionario.id_funcionarios}"></span>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</section>
<script>
    {literal}
    $(function () {
        $('#listagemFuncionarios').DataTable({
            "language": {
                "sEmptyTable": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "_MENU_ resultados por página",
                "sLoadingRecords": "Carregando...",
                "sProcessing": "Processando...",
                "sZeroRecords": "Nenhum registro encontrado",
                "sSearch": "Pesquisar",
                "oPaginate": {
                    "sNext": "Próximo",
                    "sPrevious": "Anterior",
                    "sFirst": "Primeiro",
                    "sLast": "Último"
                },
                "oAria": {
                    "sSortAscending": ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            }
        });
    });
    {/literal}
</script>