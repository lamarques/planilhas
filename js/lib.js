function Redirect(url) {
    document.location = url;
}
function NovaPag(url) {
    window.open(url);
}
function GetId(id) {
    return document.getElementById(id);
}
function GetName(nome) {
    return document.getElementsByName(nome);
}
function GetFormElemento(form, campo) {
    return document.forms[form].elements[campo].value;
}
function ResetForm(form) {
    GetId(form).reset();
}
function DesabilitaCampo(campo) {
    campo.disabled = true;
}
function HabilitaCampo(campo) {
    campo.disabled = false;
}
function LimpaDivConteudo() {
    GetId("divConteudo").innerHTML = '';
}

function MarcaCampo(id) {
    if (GetId(id).type == 'checkbox') {
        if (GetId(id).checked) {
            GetId(id).checked = false;
        } else {
            GetId(id).checked = true;
        }
    } else {
        GetId(id).checked = true;
    }

    eval(GetId(id).getAttribute('onclick'));
}

function GravaDireto(campo, tabela, coluna, id, valida, maxLength) {
    var conteudo = campo.innerHTML.trim();
    var keyPress = "if(event.keyCode == 13)AlteraValorPadrao(this, '"+tabela+"', null, null, null, true);if(event.keyCode == 27)this.parentNode.innerHTML = '"+conteudo+"';";
    campo.innerHTML = "<input maxlength="+maxLength+" id="+id+" name="+coluna+" type='text' class='inputBusca' style='width: 90%; margin: auto;' value='"+conteudo+"' valida='"+valida+"' onkeypress=\""+keyPress+"\" />";
    Valida.AddEventosMascara(campo.childNodes[0]);
    campo.childNodes[0].select();
}

function ChecarCapsLock(ev) {
    var e = ev || window.event;
    var codigo_tecla = e.keyCode ? e.keyCode : e.which;
    var tecla_shift = false;
    if (e.shiftKey)
        tecla_shift = e.shiftKey;
    else if (e.modifiers)
        tecla_shift = !!(e.modifiers & 4);
    if (((codigo_tecla >= 65 && codigo_tecla <= 90) && !tecla_shift) || ((codigo_tecla >= 97 && codigo_tecla <= 122) && tecla_shift))
        GetId('aviso_caps_lock').style.visibility = 'visible';
    else
        GetId('aviso_caps_lock').style.visibility = 'hidden';
}
function CriaAjax() {
    var objAjax;
    if (window.XMLHttpRequest) {
        objAjax = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        try {
            objAjax = new ActiveXObject('Msxml2.XMLHTTP');
        } catch (e1) {
            try {
                objAjax = new ActiveXObject('Microsoft.XMLHTTP');
            } catch (e2) {
                alert('infelizmente seu browser nao pode rodar esse sistema...');
                return false;
            }
        }
    }
    return objAjax;
}
function Load(pagina, div) {
    var LoadAjax = new RequestObject();
    LoadAjax.Solicitar({
        url: pagina,
        metodo: 'GET',
        onLoad: function(Req) {
            if (/session_erro/i.test(Req.responseText)) {
                Redirect('login.php');
            } else {
                GetId(div).innerHTML = '';
                GetId(div).innerHTML = Req.responseText;
                Valida.ProcuraForm();
                var scripts = GetId(div).getElementsByTagName("script");
                for (var i = 0; i < scripts.length; i++) {
                    var script = scripts[i].innerHTML;
                    eval(script);
                }
            }
        },
        onStateChange: function(req, id) {
            if (req.readyState != 4) {
                GetId(div).innerHTML = '<div id="divLoad"><i class="fa fa-spinner fa-pulse"></i></div>';
            }
        },
        onError: function(rq, id, msg) {
            GetId(div).innerHTML = "Load erro(Arquivo nao encontrado):\n" + msg;
        }
    });
}

function LoginAdmin(form) {
    if (!Valida.ValidaForm(form)) {
        return false;
    }
    var LoginAjax = new RequestObject();
    LoginAjax.Solicitar({
        url: 'acao_login.php',
        dados: "acao=LoginAdmin&" + PegaDados.Formulario(form) + "&width=" + $(window).width() + "&height=" + $(window).height(),
        onLoad: function(Req, id) {
            var json = eval('(' + Req.responseText + ')');
            switch (json.resultado) {
                case 'sim':
                    Redirect('index.php');
                    break;
                case 'nao':
                    alert("Nome ou senha invalidos!");
                    break;
                case 'sem_permissao':
                    alert(json.mensagem);
                    break;
                case 'inserir':
                    if (confirm("O banco deve ser inicializado,deseja fazer isso neste momento?"))
                        Load('acao_login.php?acao=iniciarBanco', 'conteudo_login');
                    else
                        alert('nao rolou');
                    break;
                case 'criar_tabela':
                    if (confirm("Deve ser criado as tabelas,deseja fazer isso neste momento?"))
                        Load('acao_login.php?acao=criarTabelas', 'conteudo_login');
                    else
                        alert('nao rolou');
                    break;
                case 'erro':
                    RetornoErro(json.erro);
                    break;
            }
        }
    });
}
function LoginAdminEnter(event, form) {
    var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
    if (keyCode == 13) {
        LoginAdmin(form);
    }
}
function LogoffAdmin() {
    var Logoff = new RequestObject();
    Logoff.Solicitar({
        url: 'acao_login.php',
        dados: "acao=LogoffAdmin",
        onLoad: function(Req, id) {
            var json = eval('(' + Req.responseText + ')');
            if (json.resultado == "sim") {
                Redirect('login.php');
            }
        }
    });
}
Ajax = {
    request: null,
    Metodo: 'POST',
    URL: null,
    dados: null,
    funcao: null,
    getXMLHttpRequest: function() {
        var objAjax;
        if (window.XMLHttpRequest) {
            objAjax = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            try {
                objAjax = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e1) {
                try {
                    objAjax = new ActiveXObject('Microsoft.XMLHTTP');
                } catch (e2) {
                    alert('infelizmente seu browser nao pode rodar esse sistema...');
                    return false;
                }
            }
        }
        return objAjax;
    },
    Solicitacao: function(url, dados, funcao) {
        this.URL = url;
        this.funcao = funcao;
        this.request = null;
        this.dados = dados ? dados : 'dados=0';
    },
    Solicitar: function() {
        this.request = this.getXMLHttpRequest();
        if (this.request) {
            this.request.onreadystatechange = this.funcao;
            this.request.open(this.Metodo, this.URL, true);
            this.request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            this.request.send(this.dados);
        }
    },
    abortar: function() {
        if (this.request) {
            this.request.abort();
        }
    }
};
var RequestObject;
function initRequest(newRequestFunc, noBody) {
    var _newRequest = newRequestFunc;
    var _noBody = noBody;
    var _id = 0;
    return function() {
        this.newRequest = _newRequest;
        this.concatTimer = function(url, id) {
            return url + (url.indexOf("?") < 0 ? "?" : "&") + "HoraReq=" + new Date().getTime() + "&ReqId=" + id;
        }
        this.Solicitar = function(object)
        {
            var url = object['url'];
            if (typeof url == 'undefined')
            {
                throw "necessário URL para fazer Solicitacao";
            }
            var id = _id++;
            var req = _newRequest();
            var metodo = object['metodo'] || "POST";
            var headers = object['header'];
            var dados = object['dados'];
            var onLoad = object['onLoad'];
            var onError = object['onError'];
            var onProcess = object['onProcess'];
            var onStateChange = object['onStateChange'];
            req.onreadystatechange = function()
            {
                if (onStateChange)
                {
                    onStateChange(req, id);
                }
                switch (req.readyState)
                {
                    case 0:
                        break;
                    case 1:
                    case 2:
                    case 3:
                        if (onProcess)
                        {
                            onProcess(req, id);
                        }
                        break;
                    case 4:
                        if (onProcess)
                        {
                            onProcess(req, id);
                        }
                        if (req.status == 0 || req.status == 200)
                        {
                            if (onLoad)
                            {
                                onLoad(req, id);
                            }
                        }
                        else
                        {
                            if (onError)
                            {
                                onError(req, id, req.statusText);
                            }
                        }
                        break;
                }
            }
            req.open(metodo, this.concatTimer(url, id));
            req.setRequestHeader('RequestId', id);
            req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            for (var header in headers)
            {
                req.setRequestHeader(header, headers[header]);
            }
            try
            {
                if (dados && _noBody && (metodo == 'GET'))
                {
                    req.send();
                }
                else
                {
                    req.send(dados);
                }
            }
            catch (e)
            {
                if (onError)
                {
                    onError(req, id, e);
                }
            }
        }
    }
}
if (window.XMLHttpRequest)
{
    try
    {
        new XMLHttpRequest();
        RequestObject = initRequest(function()
        {
            return new XMLHttpRequest();
        }, false);
    }
    catch (e) {
    }
}
else if (window.ActiveXObject)
{
    try
    {
        new ActiveXObject('Msxml2.XMLHTTP');
        RequestObject = initRequest(function()
        {
            return new ActiveXObject('Msxml2.XMLHTTP');
        }, true);
    }
    catch (e1)
    {
        try
        {
            new ActiveXObject('Microsoft.XMLHTTP');
            RequestObject = initRequest(function()
            {
                return new ActiveXObject('Microsoft.XMLHTTP');
            }, true);
        }
        catch (e2)
        {
            alert('infelizmente seu browser nao pode rodar esse sistema...');
        }
    }
}
Upload = {
    Upar: function(form) {
        var iframe = document.createElement("iframe");
        iframe.setAttribute("id", "IframeTemporario");
        iframe.setAttribute("name", "IframeTemporario");
        iframe.setAttribute("width","0");
        iframe.setAttribute("height","0");
        iframe.setAttribute("border","0");
        iframe.setAttribute("style", "width: 1000px; height: 1000px; border: none;");
        form.parentNode.appendChild(iframe);
        window.frames['IframeTemporario'].name = "IframeTemporario";

        var Carregou = function() {
            if (Upload.GetId('AguardandoUpload')) {
                Upload.GetId('AguardandoUpload').innerHTML = '';
            }
            Upload.RemoveEvento(Upload.GetId('IframeTemporario'), "load", Carregou);
            var conteudo = Upload.ConteudoIframe();
            setTimeout(function() {
                eval(Upload.decodeHTMLEntities(conteudo));
            }, 0);
        };
        Upload.AddEvento(Upload.GetId('IframeTemporario'), "load", Carregou);
        form.setAttribute("target", "IframeTemporario");
        form.setAttribute("method", "post");
        form.setAttribute("enctype", "multipart/form-data");
        form.setAttribute("encoding", "multipart/form-data");
        form.submit();
        if (GetId('AguardandoUpload')) {
            GetId('AguardandoUpload').innerHTML = '<img src="imagem/aguardando.gif">';
        }
    },
    decodeHTMLEntities: function(text) {
        var entities = [
            ['apos', '\''],
            ['amp', '&'],
            ['lt', '<'],
            ['gt', '>']
        ];
        for (var i = 0, max = entities.length; i < max; ++i) {
            text = text.replace(new RegExp('&' + entities[i][0] + ';', 'g'), entities[i][1]);
        }
        return text;
    },
    ConteudoIframe: function() {
        var io = document.getElementsByTagName('iframe')[0];
        if (io.contentWindow) {
            return io.contentWindow.document.body.innerHTML;
        } else if (io.contentDocument) {
            return io.contentDocument.document.body.innerHTML;
        }
    },
    removeIfreme: function(quem) {
        quem.parentNode.removeChild(quem);
    },
    GetId: function(elemento) {
        return document.getElementById(elemento);
    },
    AddEvento: function(campo, evento, funcao, tmp) {
        tmp || (tmp = true);
        if (campo.attachEvent) {
            campo["e" + evento + funcao] = funcao;
            campo[evento + funcao] = function() {
                campo["e" + evento + funcao](window.event);
            };
            campo.attachEvent("on" + evento, campo[evento + funcao]);
        } else {
            campo.addEventListener(evento, funcao, true);
        }
    },
    RemoveEvento: function(campo, evento, funcao, tmp) {
        tmp || (tmp = true);
        try {
            if (campo.detachEvent) {
                campo.detachEvent("on" + evento, campo[evento + funcao]);
                campo[evento + funcao] = null;
            } else {
                campo.removeEventListener(evento, funcao, true);
            }
        } catch (err) {
        }
    }
}
BuscaEmpresa = {
    EscolhaTipoBusca: function() {
        var criterio = GetId('criterio').value;
        var tdBuscaCriterio = GetId('tdBuscaCriterio');
        tdBuscaCriterio.removeChild(GetId('pesquisa'));
        var inputPesquisa = document.createElement('input');
        inputPesquisa.setAttribute('type', 'text');
        inputPesquisa.className = 'campo width200';
        inputPesquisa.setAttribute('id', 'pesquisa');
        inputPesquisa.setAttribute('name', 'pesquisa');
        switch (criterio) {
            case 'cpf':
                inputPesquisa.setAttribute('valida', 'sim,cpf,Pesquisa');
                Valida.AddEventosMascara(inputPesquisa);
                break;
            case 'cnpj':
                inputPesquisa.setAttribute('valida', 'sim,cnpj,Pesquisa');
                Valida.AddEventosMascara(inputPesquisa);
                break;
            case 'razao_social':
                inputPesquisa.setAttribute('valida', 'sim,,Pesquisa');
                break;
            case 'fantasia':
                inputPesquisa.setAttribute('valida', 'sim,,Pesquisa');
                break;
            case 'nome':
                inputPesquisa.setAttribute('valida', 'sim,,Pesquisa');
                break;
        }
        tdBuscaCriterio.appendChild(inputPesquisa);
        inputPesquisa.focus();
    },
    Busca: function(botao) {
        DesabilitaCampo(botao);
        var form = 'busca_empresa';
        if (!Valida.ValidaForm(form)) {
            HabilitaCampo(botao);
            return false;
        }
        var BuscaAjax = new RequestObject();
        BuscaAjax.Solicitar({
            url: "acao.php",
            dados: "acao=BuscarEmpresa&" + PegaDados.Formulario(form),
            onLoad: function(Req, id) {
                var json = eval('(' + Req.responseText + ')');
                switch (json.resultado) {
                    case 'sim':
                        BuscaEmpresa.RemoveTodasLinhaGridPesquisa();
                        for (var i in json.empresas) {
                            BuscaEmpresa.AddLinhaGridPesquisa(json.empresas[i]);
                        }
                        HabilitaCampo(botao);
                        break;
                    case 'dados_errados':
                        var erro = dadosErrados(json.dados_errados);
                        var msg = eval("Valida.Msg" + Valida.LinguaForm(form).toUpperCase());
                        alert(msg + " \n\t- " + erro.join("\n\t- "));
                        HabilitaCampo(botao);
                        break;
                    case 'msg_erro':
                        var erro = dadosErrados(json.msg_erro);
                        alert(erro.join("\n\t- "));
                        HabilitaCampo(botao);
                        break;
                    case 'erro':
                        RetornoErro(json.erro);
                        HabilitaCampo(botao);
                        break;
                }
            },
            onStateChange: function(req, id) {
                if (req.readyState != 4)
                    GetId('aguardandoPesquisa').innerHTML = '<img src="imagem/aguardando.gif">';
                else
                    GetId('aguardandoPesquisa').innerHTML = '';
            },
            onError: function(rq, id, msg) {
                alert('Ocorreu um problema tente novamente em instantes, caso persista contate o administrador.');
            }
        });
    },
    AddLinhaGridPesquisa: function(empresa) {
        var razao_social = empresa.razao_social ? empresa.razao_social : '';
        var nome = empresa.nome ? empresa.nome : '';
        var documento = empresa.documento ? empresa.documento : '';
        var nome_fantasia = empresa.nome_fantasia ? empresa.nome_fantasia : '';
        var tabela = GetId("tabela_busca_empresa");
        var numeroLinhas = tabela.rows.length;
        var newRow = tabela.insertRow(numeroLinhas - 1);
        ColunaRazaoNome = newRow.insertCell(0);
        ColunaRazaoNome.className = 'Center TbListagem';
        ColunaRazaoNome.innerHTML = razao_social + '' + nome;
        var InputRazaoNome = document.createElement('input');
        InputRazaoNome.setAttribute('type', 'hidden');
        InputRazaoNome.setAttribute('id', 'buscaRazaoNome' + empresa.id_empresa);
        InputRazaoNome.setAttribute('name', 'buscaRazaoNome' + empresa.id_empresa);
        InputRazaoNome.setAttribute('value', razao_social + '' + nome);
        ColunaRazaoNome.appendChild(InputRazaoNome);
        var InputDocumento = document.createElement('input');
        InputDocumento.setAttribute('type', 'hidden');
        InputDocumento.setAttribute('id', 'buscaDocumento' + empresa.id_empresa);
        InputDocumento.setAttribute('name', 'buscaDocumento' + empresa.id_empresa);
        InputDocumento.setAttribute('value', documento);
        ColunaRazaoNome.appendChild(InputDocumento);
        ColunaFantasia = newRow.insertCell(1);
        ColunaFantasia.className = 'Center TbListagem';
        ColunaFantasia.innerHTML = empresa.nome_fantasia;
        var InputDocumento = document.createElement('input');
        InputDocumento.setAttribute('type', 'hidden');
        InputDocumento.setAttribute('id', 'buscaCNPJ' + empresa.documento);
        InputDocumento.setAttribute('name', 'buscaCNPJ' + empresa.documento);
        InputDocumento.setAttribute('value', documento);
        ColunaRazaoNome.appendChild(InputDocumento);
        ColunaFantasia = newRow.insertCell(2);
        ColunaFantasia.className = 'Center TbListagem';
        ColunaFantasia.innerHTML = empresa.documento;
        ColunaSelecione = newRow.insertCell(3);
        ColunaSelecione.className = 'Center TbListagem';
        var InputSelecione = document.createElement('input');
        InputSelecione.setAttribute('type', 'radio');
        InputSelecione.setAttribute('id', 'buscaIdEmpresa' + empresa.id_empresa);
        InputSelecione.setAttribute('name', 'buscaIdEmpresa');
        InputSelecione.setAttribute('value', empresa.id_empresa);
        ColunaSelecione.appendChild(InputSelecione);
    },
    RemoveLinhaGridPesquisa: function(obj) {
        var delRow = obj.parentNode.parentNode;
        var rIndex = delRow.sectionRowIndex;
        var tabela = GetId("tabela_busca_empresa");
        tabela.deleteRow(rIndex);
    },
    RemoveTodasLinhaGridPesquisa: function() {
        var frm = document.forms['form_busca_empresa'];
        for (var i = 0; i < frm.elements.length; i++) {
            var campo = frm.elements[i];
            if (campo.id.indexOf('buscaIdEmpresa') == 0) {
                this.RemoveLinhaGridPesquisa(campo);
                i = -1;
            }
        }
    },
    RetornaEmpresaBusca: function(objeto, extra) {
        objeto = (objeto) ? objeto : obj = [];
        var campoIdEmpresa = objeto['id_empresa'] || 'id_empresa';
        var campoRazaoNome = objeto['razao_social'] || 'razao_social';
        var campoDocumento = objeto['documento'] || 'documento';
        var tdRazaoNome = objeto['tdRazaoNome'] || 'tdRazaoNome';
        var obj = GetName('buscaIdEmpresa');
        var marcado = false;
        for (var i = 0; i < obj.length; i++) {
            if (obj[i].checked) {
                marcado = true;
                valorIdEmpresa = obj[i].value;
            }
        }
        if (!marcado) {
            alert('Deve ser selecionado uma empresa!');
            return false;
        }
        if (GetId(campoIdEmpresa))
            GetId(campoIdEmpresa).value = valorIdEmpresa;
        if (GetId(campoRazaoNome))
            GetId(campoRazaoNome).value = GetId('buscaRazaoNome' + valorIdEmpresa).value;
        if (GetId(campoDocumento))
            GetId(campoDocumento).value = GetId('buscaDocumento' + valorIdEmpresa).value;
        if (GetId(tdRazaoNome))
            GetId(tdRazaoNome).innerHTML = GetId('buscaRazaoNome' + valorIdEmpresa).value;
        if (extra == "CadastroFinanceiro")
            FinanceiroCadastroDocumento.liberaCampos('buscaEmpresa');
        RemovePopup(2);
    }
}
var Drag = {
    obj: null,
    init: function(o, oRoot, minX, maxX, minY, maxY, bSwapHorzRef, bSwapVertRef, fXMapper, fYMapper)
    {
        o.onmousedown = Drag.start;
        o.hmode = bSwapHorzRef ? false : true;
        o.vmode = bSwapVertRef ? false : true;
        o.root = oRoot && oRoot != null ? oRoot : o;
        if (o.hmode && isNaN(parseInt(o.root.style.left)))
            o.root.style.left = "0px";
        if (o.vmode && isNaN(parseInt(o.root.style.top)))
            o.root.style.top = "0px";
        if (!o.hmode && isNaN(parseInt(o.root.style.right)))
            o.root.style.right = "0px";
        if (!o.vmode && isNaN(parseInt(o.root.style.bottom)))
            o.root.style.bottom = "0px";
        o.minX = typeof minX != 'undefined' ? minX : null;
        o.minY = typeof minY != 'undefined' ? minY : null;
        o.maxX = typeof maxX != 'undefined' ? maxX : null;
        o.maxY = typeof maxY != 'undefined' ? maxY : null;
        o.xMapper = fXMapper ? fXMapper : null;
        o.yMapper = fYMapper ? fYMapper : null;
        o.root.onDragStart = new Function();
        o.root.onDragEnd = new Function();
        o.root.onDrag = new Function();
    },
    start: function(e)
    {
        var o = Drag.obj = this;
        e = Drag.fixE(e);
        var y = parseInt(o.vmode ? o.root.style.top : o.root.style.bottom);
        var x = parseInt(o.hmode ? o.root.style.left : o.root.style.right);
        o.root.onDragStart(x, y);
        o.lastMouseX = e.clientX;
        o.lastMouseY = e.clientY;
        if (o.hmode) {
            if (o.minX != null)
                o.minMouseX = e.clientX - x + o.minX;
            if (o.maxX != null)
                o.maxMouseX = o.minMouseX + o.maxX - o.minX;
        } else {
            if (o.minX != null)
                o.maxMouseX = -o.minX + e.clientX + x;
            if (o.maxX != null)
                o.minMouseX = -o.maxX + e.clientX + x;
        }
        if (o.vmode) {
            if (o.minY != null)
                o.minMouseY = e.clientY - y + o.minY;
            if (o.maxY != null)
                o.maxMouseY = o.minMouseY + o.maxY - o.minY;
        } else {
            if (o.minY != null)
                o.maxMouseY = -o.minY + e.clientY + y;
            if (o.maxY != null)
                o.minMouseY = -o.maxY + e.clientY + y;
        }
        document.onmousemove = Drag.drag;
        document.onmouseup = Drag.end;
        return false;
    },
    drag: function(e)
    {
        e = Drag.fixE(e);
        var o = Drag.obj;
        var ey = e.clientY;
        var ex = e.clientX;
        var y = parseInt(o.vmode ? o.root.style.top : o.root.style.bottom);
        var x = parseInt(o.hmode ? o.root.style.left : o.root.style.right);
        var nx, ny;
        if (o.minX != null)
            ex = o.hmode ? Math.max(ex, o.minMouseX) : Math.min(ex, o.maxMouseX);
        if (o.maxX != null)
            ex = o.hmode ? Math.min(ex, o.maxMouseX) : Math.max(ex, o.minMouseX);
        if (o.minY != null)
            ey = o.vmode ? Math.max(ey, o.minMouseY) : Math.min(ey, o.maxMouseY);
        if (o.maxY != null)
            ey = o.vmode ? Math.min(ey, o.maxMouseY) : Math.max(ey, o.minMouseY);
        nx = x + ((ex - o.lastMouseX) * (o.hmode ? 1 : -1));
        ny = y + ((ey - o.lastMouseY) * (o.vmode ? 1 : -1));
        if (o.xMapper)
            nx = o.xMapper(y)
        else if (o.yMapper)
            ny = o.yMapper(x)
        Drag.obj.root.style[o.hmode ? "left" : "right"] = nx + "px";
        Drag.obj.root.style[o.vmode ? "top" : "bottom"] = ny + "px";
        Drag.obj.lastMouseX = ex;
        Drag.obj.lastMouseY = ey;
        Drag.obj.root.onDrag(nx, ny);
        return false;
    },
    end: function()
    {
        document.onmousemove = null;
        document.onmouseup = null;
        Drag.obj.root.onDragEnd(parseInt(Drag.obj.root.style[Drag.obj.hmode ? "left" : "right"]),
                parseInt(Drag.obj.root.style[Drag.obj.vmode ? "top" : "bottom"]));
        Drag.obj = null;
    },
    fixE: function(e)
    {
        if (typeof e == 'undefined')
            e = window.event;
        if (typeof e.layerX == 'undefined')
            e.layerX = e.offsetX;
        if (typeof e.layerY == 'undefined')
            e.layerY = e.offsetY;
        return e;
    }
};
var PegaDados = {
    Informacoes: "",
    valida: '',
    arrayDados: '',
    Formulario: function(form, valida) {
        this.arrayDados = new Object();
        this.valida = (valida == undefined || valida == '') ? true : false;
        this.Informacoes = "form=" + form;
        var frm = document.forms[form];
        var numElementos = frm.elements.length;
        for (var i = 0; i < numElementos; i++) {
            var campo = frm.elements[i];
            switch (campo.type) {
                case "radio":
                    this.PegaDadosRADIO(campo);
                    break;
                case "checkbox":
                    this.PegaDadosCHECKBOX(campo);
                    break;
                case 'select-multiple' :
                    this.PegaDadosSELECTMULTIPLE(campo);
                    break;
                case "button":
                case "reset":
                case "submit":
                    break;
                default:
                    this.PegaDadosPADRAO(campo);
                    break;
            }
        }
        return this.Informacoes;
    },
    PegaDadosPADRAO: function(campo) {
        if (this.Informacoes.indexOf('&' + campo.name + '=') == -1) {
            this.arrayDados[campo.name] = campo.value;
            if (campo.getAttribute("valida") == null) {
                if (this.valida)
                    this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(campo.value) + "&" + campo.name + "_Valida=" + encodeURIComponent(",,," + campo.type);
                else
                    this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(campo.value);
            } else {
                if (this.valida)
                    this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(campo.value) + "&" + campo.name + "_Valida=" + encodeURIComponent(campo.getAttribute("valida") + "," + campo.type);
                else
                    this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(campo.value);
            }
        }
    },
    PegaDadosCHECKBOX: function(campo) {
        if (campo.checked == true)
            this.PegaDadosPADRAO(campo);
        else if(campo.getAttribute('data-salvar') === 'sim')
        {
            campo.value = 'false';
            this.PegaDadosPADRAO(campo);
        }
    },
    PegaDadosRADIO: function(campo) {
        var obj = this.GetName(campo.name);
        for (var i = 0; i < obj.length; i++) {
            if (obj[i].checked)
                this.PegaDadosPADRAO(obj[i]);
        }
    },
    PegaDadosSELECTMULTIPLE: function(campo) {
        var valores = '';
        for (var i = 0; i < campo.options.length; i++) {
            if (campo.options[i].selected) {
                if (valores == '')
                    valores = campo.options[i].value;
                else
                    valores += ';' + campo.options[i].value;
            }
        }
        if (campo.getAttribute("valida") == null) {
            if (this.valida)
                this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(valores) + "&" + campo.name + "_Valida=" + encodeURIComponent(",,," + campo.type);
            else
                this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(valores);
        } else {
            if (this.valida)
                this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(valores) + "&" + campo.name + "_Valida=" + encodeURIComponent(campo.getAttribute("valida") + "," + campo.type);
            else
                this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(valores);
        }
    },
    GetName: function(elemento) {
        return document.getElementsByName(elemento);
    },
    getArrayDados: function(form, valida) {
        this.Formulario(form, valida);
        return this.arrayDados;
    }
};
var Imagem = {
    Inserir: function(form) {
        if (!Valida.ValidaForm(form)) {
            return false;
        }
        Upload.Upar(GetId(form));
    },
    Excluir: function(botao, id, nome, pagina, div) {
        DesabilitaCampo(botao);
        if (!confirm("Deseja Realmente Excluir?")) {
            HabilitaCampo(botao);
            return false;
        }
        var Retorno = function() {
            var ajax = Ajax.request;
            if (ajax.readyState == 4) {
                GetId('AguardandoExcluir' + id).innerHTML = '';
                if (ajax.status == 200) {
                    var json = eval('(' + ajax.responseText + ')');
                    switch (json.resultado) {
                        case 'sim':
                            HabilitaCampo(botao);
                            if (pagina && div)
                                Load(pagina, div);
                            break;
                        case 'erro':
                            RetornoErro(json.erro);
                            HabilitaCampo(botao);
                            break;
                    }
                }
            } else if (ajax.readyState != 4) {
                GetId('AguardandoExcluir' + id).innerHTML = '<img src="../imagem/aguardando.gif">';
            }
        };
        var valores = "acao=ExcluirImagem&id_form=" + id + "&nome_img=" + nome + "&";
        Ajax.Solicitacao("secretaria/acao.php", valores, Retorno);
        Ajax.Solicitar();
    }
}
function RemovePopup(camada) {
    var i = camada - 1;
    if (!camada) {
        camada = 1;
    }
    if (GetId('divPopup' + camada)) {
        valor = GetId('divPopup' + camada);
        window.document.body.removeChild(valor);
    }
    if (GetId('camadaCaixaFormulario' + i)) {
        GetId('camadaCaixaFormulario' + i).style.opacity = 1.0;
    }
    document.body.style.overflow = 'auto';
    document.body.scroll = "yes";
}
function CriaPopup(pagina, formWidth, camada) {
    if (!camada) {
        camada = 1;
    }
    var i;
    var z_index = camada;
    var PageSizes = TamanhoPagina();
    var width = PageSizes[0];
    var height = PageSizes[1];
    var PageScroll = TamanhoScroll();
    FormTop = PageScroll[1] + (PageSizes[3] / 3);
    formWidth = ((formWidth != '') && (formWidth)) ? formWidth : 500;
    formWidth = parseInt(formWidth) + parseInt(getScrollerWidth());

    var formulario = window.document.createElement('DIV');
    formulario.setAttribute('id', "divPopup" + camada);
    document.body.style.overflow = 'hidden';
    document.body.scroll = "no";
    document.body.setAttribute('onkeyup', 'verify(event,' + camada + ')');
    document.body.setAttribute('onkeyup', 'verify(event,' + camada + ')');
    var html = "";
//    html += "<script>";
//    html += "   {literal}";
//    html += "       if(!document.addEventListener && document.attachEvent) { ";
//    html += "           document.attachEvent('onkeydown', function(){verify(event,"+camada+")}); ";
//    html += "       } else { ";
//    html += "           document.addEventListener('keydown',function(event){verify(event,"+camada+");},false); ";
//    html += "       } ";
//    html += "   {/literal}";
//    html += "</script>";
    html += '<div id="camadaFundoTransparente' + camada + '" class="divFundoTransparente" style="height: ' + height + 'px; z_index=' + z_index + '" >&nbsp;</div>';
    html += '<div id="camadaFundoCentralizado' + camada + '" class="divFundoCentralizado" style="height: ' + height + 'px; z_index=' + (z_index + 1) + '" >';
    html += '<div id="camadaCaixaFormulario' + camada + '" class="divCaixaFormulario" style="top: ' + FormTop + 'px; width: ' + formWidth + 'px; height:auto;" >';
    html += '<div id="camadaBotaoSair' + camada + '" class="divBotaoSair" style="vertical-align:midle;">';
    html += '<input type="button" class="btnExcluir" onclick="RemovePopup(' + camada + ');" />';
    html += '</div>';
    var alturaMaximo = window.innerHeight - (window.innerHeight * 0.15);
    html += '<div id="divFormularios' + camada + '" style="overflow: auto; max-height: ' + alturaMaximo + 'px; padding-top: 10px;">';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    formulario.innerHTML = html;
    window.document.body.appendChild(formulario);
    Load(pagina, 'divFormularios' + camada, undefined, camada);
    PageSizes = TamanhoPagina();
    width = PageSizes[0];
    height = PageSizes[1];
    PageScroll = TamanhoScroll();

    FormTop = 0;
    GetId('camadaFundoTransparente' + camada).style.height = height + "px";
    GetId('camadaFundoCentralizado' + camada).style.height = height + "px";
    GetId('camadaCaixaFormulario' + camada).style.top = FormTop + "px";
    GetId('camadaCaixaFormulario' + camada).style.position = 'fixed';
    var diferenca = (window.innerWidth - formWidth) / 2;
    GetId('camadaCaixaFormulario' + camada).style.left = diferenca + 'px';
    Drag.init(GetId('camadaBotaoSair' + camada), GetId('camadaCaixaFormulario' + camada), null, null, 0);
    for (i = camada - 1; i > 0; i--) {
        GetId('camadaCaixaFormulario' + i).style.opacity = 0.3;
    }

}

function getScrollerWidth() {
    var scr = null;
    var inn = null;
    var wNoScroll = 0;
    var wScroll = 0;

    // Outer scrolling div
    scr = document.createElement('div');
    scr.style.position = 'absolute';
    scr.style.top = '-1000px';
    scr.style.left = '-1000px';
    scr.style.width = '100px';
    scr.style.height = '50px';
    // Start with no scrollbar
    scr.style.overflow = 'hidden';

    // Inner content div
    inn = document.createElement('div');
    inn.style.width = '100%';
    inn.style.height = '200px';

    // Put the inner div in the scrolling div
    scr.appendChild(inn);
    // Append the scrolling div to the doc
    document.body.appendChild(scr);

    // Width of the inner div sans scrollbar
    wNoScroll = inn.offsetWidth;
    // Add the scrollbar
    scr.style.overflow = 'auto';
    // Width of the inner div width scrollbar
    wScroll = inn.offsetWidth;

    // Remove the scrolling div from the doc
    document.body.removeChild(
            document.body.lastChild);

    // Pixel width of the scroller
    return (wNoScroll - wScroll);
}

function CriaPopupBranco(pagina, formWidth, camada) {
    if (!camada)
        camada = 1;
    var z_index = camada;
    var PageSizes = TamanhoPagina();
    var width = PageSizes[0];
    var height = PageSizes[1];
    var PageScroll = TamanhoScroll();
    FormTop = PageScroll[1] + (PageSizes[3] / 3);
    formWidth = ((formWidth != '') && (formWidth)) ? formWidth : 500;
    var formulario = window.document.createElement('DIV');
    formulario.setAttribute('id', "divPopup" + camada);

    var html = "<script>";
    html += " if(!document.addEventListener && document.attachEvent) { document.attachEvent('onkeydown', function(){verify(event," + camada + ")}); } else {document.addEventListener('keydown',function(event){verify(event," + camada + ");},false);}";
    html += "</script>";
    html += '<div id="camadaFundoTransparente' + camada + '" class="divFundoTransparente" style="height: ' + height + 'px; z_index=' + z_index + '" >&nbsp;</div>';
    html += '<div id="camadaFundoCentralizado' + camada + '" class="divFundoCentralizado" style="height: ' + height + 'px; z_index=' + (z_index + 1) + '" >';
    html += '<div id="camadaCaixaFormulario' + camada + '" class="divCaixaFormulario" style="top: ' + FormTop + 'px; width: ' + formWidth + 'px; height:auto;" >';
    html += '<div id="camadaBotaoSair' + camada + '" class="divBotaoSair">';
    html += '<input type="button" class="BotaoFechar" onclick="RemovePopup(' + camada + ');" />';
    html += '</div>';
    html += '<div id="divFormularios' + camada + '">';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    formulario.innerHTML = html;
    window.document.body.appendChild(formulario);
    GetId('divFormularios' + camada).innerHTML = pagina;
    PageSizes = TamanhoPagina();
    width = PageSizes[0];
    height = PageSizes[1];
    PageScroll = TamanhoScroll();
    FormTop = PageScroll[1] + (PageSizes[3] / 10);
    GetId('camadaFundoTransparente' + camada).style.height = height + "px";
    GetId('camadaFundoCentralizado' + camada).style.height = height + "px";
    GetId('camadaCaixaFormulario' + camada).style.top = FormTop + "px";
    Drag.init(GetId('camadaBotaoSair' + camada), GetId('camadaCaixaFormulario' + camada), null, null, 0);

    verify(event, "+camada+");
}
function verify(e, camada)
{
    var tecla = false;
    if (navigator.appName.indexOf('Internet Explorer') > 0) {
        tecla = e.keyCode;
    } else {
        tecla = e.which;
    }
//    if (tecla == 27) {
//        RemovePopup(camada);
//        if (camada > 1) {
//            document.body.setAttribute('onkeyup', 'verify(event,' + (camada - 1) + ')');
//        } else {
//            document.body.removeAttribute('onkeyup');
//        }
//    }
}
function TamanhoPagina() {
    var xScroll, yScroll;
    if (window.innerHeight && window.scrollMaxY) {
        xScroll = window.innerWidth + window.scrollMaxX;
        yScroll = window.innerHeight + window.scrollMaxY;
    } else if (document.body.scrollHeight > document.body.offsetHeight) {
        xScroll = document.body.scrollWidth;
        yScroll = document.body.scrollHeight;
    } else {
        xScroll = document.body.offsetWidth;
        yScroll = document.body.offsetHeight;
    }
    var windowWidth, windowHeight;
    if (self.innerHeight) {
        if (document.documentElement.clientWidth) {
            windowWidth = document.documentElement.clientWidth;
        } else {
            windowWidth = self.innerWidth;
        }
        windowHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) {
        windowWidth = document.documentElement.clientWidth;
        windowHeight = document.documentElement.clientHeight;
    } else if (document.body) {
        windowWidth = document.body.clientWidth;
        windowHeight = document.body.clientHeight;
    }
    if (yScroll < windowHeight) {
        pageHeight = windowHeight;
    } else {
        pageHeight = yScroll;
    }
    if (xScroll < windowWidth) {
        pageWidth = xScroll;
    } else {
        pageWidth = windowWidth;
    }
    arrayPageSize = new Array(pageWidth, pageHeight, windowWidth, windowHeight);
    return arrayPageSize;
}
function TamanhoScroll() {
    var xScroll, yScroll;
    if (self.pageYOffset) {
        yScroll = self.pageYOffset;
        xScroll = self.pageXOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {
        yScroll = document.documentElement.scrollTop;
        xScroll = document.documentElement.scrollLeft;
    } else if (document.body) {
        yScroll = document.body.scrollTop;
        xScroll = document.body.scrollLeft;
    }
    arrayPageScroll = new Array(xScroll, yScroll);
    return arrayPageScroll;
}
Cep = {
    BuscaEndereco: function(objeto) {
        objeto = (objeto) ? objeto : obj = [];
        var cep = objeto['cep'] || 'cep';
        var logradouro = objeto['logradouro'] || 'logradouro';
        var cidade = objeto['cidade'] || 'cidade';
        var bairro = objeto['bairro'] || 'bairro';
        var numero = objeto['numero'] || 'numero';
        var id_estado = objeto['id_estado'] || 'id_estado';
        var uf = objeto['uf'] || 'uf';
        if (!GetId(cep)) {
            alert('Campo CEP nao encontrado contate o administrador!');
            return false;
        }
        var campo = GetId(cep);
        if (!(/^[0-9]{5}-[0-9]{3}$/i.test(campo.value))) {
            return false;
        }
        var Retorno = function() {
            var ajax = Ajax.request;
            if (ajax.readyState == 4) {
                if (GetId('div_status_' + cep))
                    GetId('div_status_' + cep).innerHTML = '';
                if (ajax.status == 200) {
                    var json = eval('(' + ajax.responseText + ')');
                    switch (json.resultado) {
                        case 'sim':
                            if (json.cep.resultado == '1') {
                                if (GetId(cidade))
                                    GetId(cidade).value = json.cep.cidade;
                                if (GetId(logradouro))
                                    GetId(logradouro).value = json.cep.tipo_logradouro + ' ' + json.cep.logradouro
                                if (GetId(bairro))
                                    GetId(bairro).value = json.cep.bairro;
                                if (GetId(id_estado))
                                    GetId(id_estado).value = json.cep.id_estado;
                                if (GetId(uf))
                                    GetId(uf).value = json.cep.uf;
                                if (GetId(numero))
                                    GetId(numero).focus();
                            } else {
                                if (GetId(cidade))
                                    GetId(cidade).value = json.cep.cidade;
                                if (GetId(id_estado))
                                    GetId(id_estado).value = json.cep.id_estado;
                                if (GetId(uf))
                                    GetId(uf).value = json.cep.uf;
                            }
                            break;
                    }
                }
            } else if (ajax.readyState != 4) {
                if (GetId('div_status_' + cep))
                    GetId('div_status_' + cep).innerHTML = '<img src="imagem/aguardando.gif">';
            }
        };
        var valores = "acao=BuscaEndereco&cep=" + campo.value;
        Ajax.Solicitacao("acao.php", valores, Retorno);
        Ajax.Solicitar();
    },
    BuscaCidadeDoEstado: function(uf, campoAguardando, idCidade) {
        if (idCidade == undefined)
            idCidade = 'busca_cep_cidade';
        var Retorno = function() {
            var ajax = Ajax.request;
            if (ajax.readyState == 4) {
                GetId(campoAguardando).innerHTML = '';
                if (ajax.status == 200) {
                    var json = eval('(' + ajax.responseText + ')');
                    switch (json.resultado) {
                        case 'sim':
                            var SelectCidade = GetId('busca_cep_cidade');
                            for (i = SelectCidade.length - 1; i >= 1; i--) {
                                SelectCidade.remove(i);
                            }
                            for (var i = 0; i < json.cidades.length; i++) {
                                var option = document.createElement('option');
                                option.text = json.cidades[i].cidade;
                                option.value = json.cidades[i].cidade;
                                try {
                                    SelectCidade.add(option, null);
                                } catch (e) {
                                    SelectCidade.add(option);
                                }
                            }
                            break;
                        case 'dados_errados':
                            var erro = dadosErrados(json.dados_errados);
                            var msg = eval("Valida.Msg" + Valida.LinguaForm(form).toUpperCase());
                            alert(msg + " \n\t- " + erro.join("\n\t- "));
                            break;
                        case 'erro':
                            RetornoErro(json.erro);
                            break;
                    }
                }
            } else if (ajax.readyState != 4) {
                GetId(campoAguardando).innerHTML = '<img src="imagem/aguardando.gif">';
            }
        };
        var valores = "acao=BuscaCidadeDoEstado&UF=" + uf;
        Ajax.Solicitacao("acao.php", valores, Retorno);
        Ajax.Solicitar();
    },
    BuscaLogradourosDaCidade: function(form, botao) {
        if (!Valida.ValidaForm(form)) {
            HabilitaCampo(botao);
            return false;
        }
        var Estado = GetId('busca_cep_estado').value;
        var Cidade = GetId('busca_cep_cidade').value;
        var estados = new Array(
                'AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO',
                'MA', 'MG', 'MT', 'MS', 'PA', 'PB', 'PE', 'PI', 'PR',
                'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO'
                );
        for (var i = 0; i < estados.length; i++) {
            if (estados[i] == Estado) {
                var id_estado = i + 1;
            }
        }
        var Retorno = function() {
            var ajax = Ajax.request;
            if (ajax.readyState == 4) {
                GetId('TdPesquisandoLogradouros').innerHTML = '';
                if (ajax.status == 200) {
                    var json = eval('(' + ajax.responseText + ')');
                    switch (json.resultado) {
                        case 'sim':
                            for (var i = 0; i < json.logradouros.length; i++) {
                                var opcao = {
                                    'cidade': Cidade,
                                    'estado': Estado,
                                    'id_estado': id_estado,
                                    'bairro': json.logradouros[i].bairro,
                                    'cep': json.logradouros[i].cep,
                                    'logradouro': json.logradouros[i].logradouro
                                }
                                Cep.AddLogradourosBuscaCep(opcao);
                            }
                            HabilitaCampo(botao);
                            break;
                        case 'dados_errados':
                            var erro = dadosErrados(json.dados_errados);
                            var msg = eval("Valida.Msg" + Valida.LinguaForm(form).toUpperCase());
                            alert(msg + " \n\t- " + erro.join("\n\t- "));
                            HabilitaCampo(botao);
                            break;
                        case 'erro':
                            RetornoErro(json.erro);
                            HabilitaCampo(botao);
                            break;
                    }
                }
            } else if (ajax.readyState != 4) {
                GetId('TdPesquisandoLogradouros').innerHTML = '<img src="imagem/aguardando.gif">';
            }
        };
        this.RemoveTotosLogradourosBuscaCep();
        var valores = "acao=BuscaLogradourosDaCidade&" + PegaDados.Formulario(form);
        Ajax.Solicitacao("acao.php", valores, Retorno);
        Ajax.Solicitar();
    },
    AddLogradourosBuscaCep: function(opcao) {
        var id = parseInt(GetId('ContadorLogradouro').value);
        GetId('ContadorLogradouro').value = ++id;
        var tabela = GetId("TabelaLogradouros");
        var numeroLinhas = tabela.rows.length;
        var newRow = tabela.insertRow(numeroLinhas);
        ColunaLogradouro = newRow.insertCell(0);
        ColunaLogradouro.className = 'Center TbListagem width200';
        ColunaLogradouro.innerHTML = opcao.logradouro;// Insere um conteúdo na coluna
        var InputLogradouro = document.createElement('input');
        InputLogradouro.setAttribute('type', 'hidden');
        InputLogradouro.setAttribute('id', 'ListaLogradouro' + id);
        InputLogradouro.setAttribute('name', 'ListaLogradouro' + id);
        InputLogradouro.setAttribute('value', opcao.logradouro);
        ColunaLogradouro.appendChild(InputLogradouro);
        var InputCidade = document.createElement('input');
        InputCidade.setAttribute('type', 'hidden');
        InputCidade.setAttribute('id', 'ListaCidade' + id);
        InputCidade.setAttribute('name', 'ListaCidade' + id);
        InputCidade.setAttribute('value', opcao.cidade);
        ColunaLogradouro.appendChild(InputCidade);
        var InputEstado = document.createElement('input');
        InputEstado.setAttribute('type', 'hidden');
        InputEstado.setAttribute('id', 'ListaEstado' + id);
        InputEstado.setAttribute('name', 'ListaEstado' + id);
        InputEstado.setAttribute('value', opcao.estado);
        ColunaLogradouro.appendChild(InputEstado);
        var InputIdEstado = document.createElement('input');
        InputIdEstado.setAttribute('type', 'hidden');
        InputIdEstado.setAttribute('id', 'ListaIdEstado' + id);
        InputIdEstado.setAttribute('name', 'ListaIdEstado' + id);
        InputIdEstado.setAttribute('value', opcao.id_estado);
        ColunaLogradouro.appendChild(InputIdEstado);
        ColunaBairro = newRow.insertCell(1);
        ColunaBairro.className = 'Center TbListagem width180';
        ColunaBairro.innerHTML = opcao.bairro;// Insere um conteúdo na coluna
        var InputBairro = document.createElement('input');
        InputBairro.setAttribute('type', 'hidden');
        InputBairro.setAttribute('id', 'ListaBairro' + id);
        InputBairro.setAttribute('name', 'ListaBairro' + id);
        InputBairro.setAttribute('value', opcao.bairro);
        ColunaBairro.appendChild(InputBairro);
        ColunaCep = newRow.insertCell(2);
        ColunaCep.className = 'Center TbListagem width100';
        ColunaCep.innerHTML = opcao.cep;// Insere um conteúdo na coluna
        var InputCep = document.createElement('input');
        InputCep.setAttribute('type', 'hidden');
        InputCep.setAttribute('id', 'ListaCep' + id);
        InputCep.setAttribute('name', 'ListaCep' + id);
        InputCep.setAttribute('value', opcao.cep);
        ColunaCep.appendChild(InputCep);
        ColunaSelecione = newRow.insertCell(3);
        ColunaSelecione.className = 'Center TbListagem width80';
        var InputSelecione = document.createElement('input');
        InputSelecione.setAttribute('type', 'radio');
        InputSelecione.setAttribute('id', 'selecione');
        InputSelecione.setAttribute('name', 'selecione');
        InputSelecione.setAttribute('value', id);
        ColunaSelecione.appendChild(InputSelecione);
    },
    RemoveLogradourosBuscaCep: function(obj) {
        var delRow = obj.parentNode.parentNode;
        var rIndex = delRow.sectionRowIndex;
        var tabela = GetId("TabelaLogradouros");
        tabela.deleteRow(rIndex);
    },
    RemoveTotosLogradourosBuscaCep: function(obj) {
        var frm = document.forms['logradouros_busca_cep'];
        for (var i = 0; i < frm.elements.length; i++) {
            var campo = frm.elements[i];
            if (campo.id.indexOf('ListaLogradouro') == 0) {
                this.RemoveLogradourosBuscaCep(campo);
                i = 0;
            }
        }
    },
    BuscarCep: function(camada) {
        CriaPopup('busca_cep.php', 650, camada);
    },
    MontaDadosForm: function(func) {
        eval(func);
    }
};
var Padrao = {
    Executa: function(objeto) {
        var camada = objeto['camada'];
        var validar = objeto['validar'];
        var form = objeto['form'];
        var botao = objeto['botao'];
        var loadPagina = objeto['loadPagina'];
        var loadDiv = objeto['loadDiv'];
        var divAguardando = objeto['divAguardando'];
        var parametros = objeto['parametros'];
        var pagina = objeto['pagina'];
        var excluir = objeto['excluir'] || false;
        var EventoSim = objeto['EventoSim'];
        var lingua = objeto['lingua'];
        var conclusao = objeto['conclusao'];
        if (lingua == null) {
            lingua = 'br';
        }
        var mensagem = '';
        switch (lingua) {
            case 'br':
                mensagem = "Deseja Realmente Excluir?";
                break;
            case 'en':
                mensagem = "Do you really want to delete?";
                break;
            case 'en':
                mensagem = "Estás seguro que quieres eliminar?";
                break;
        }
        if (botao) {
            DesabilitaCampo(botao);
        }
        if (validar) {
            if (!Valida.ValidaForm(form)) {
                if (botao) {
                    HabilitaCampo(botao);
                }
                return false;
            }
        } else if (excluir) {
            if (!confirm(mensagem)) {
                HabilitaCampo(botao);
                return false;
            }
        }
        var dadosForm = '';
        if (form) {
            dadosForm = "&" + PegaDados.Formulario(form);
        }
        var ExecutaPadrao = new RequestObject();
        ExecutaPadrao.Solicitar({
            url: pagina,
            dados: parametros + dadosForm,
            onLoad: function(Req, id) {
                var json = eval('(' + Req.responseText + ')');
                switch (json.resultado) {
                    case 'sim':
                        if (typeof conclusao == 'undefined' || conclusao === true) {
                            RetornoPositivo(json.retorno, lingua);
                            if (EventoSim) {
                                EventoSim(json);
                            }
                            if (botao) {
                                HabilitaCampo(botao);
                            }
                            if (camada) {
                                RemovePopup(camada);
                            }
                            if (loadPagina && loadDiv) {
                                Load(loadPagina, loadDiv);
                            }
                            if (json.cria_popup == 'sim') {
                                CriaPopup(json.pagina_popup, json.form_width);
                            }
                        }
                        break;
                    case 'personalizado':
                        if (botao) {
                            HabilitaCampo(botao);
                        }
                        eval(json.retorno);
                        break;
                    case 'mensagem':
                        alert(json.mensagem);
                        if (botao) {
                            HabilitaCampo(botao);
                        }
                        break;
                    case 'dados_errados':
                        var erro = dadosErrados(json.dados_errados);
                        var msg = eval("Valida.Msg" + Valida.LinguaForm(form).toUpperCase());
                        alert(msg + " \n\t- " + erro.join("\n\t- "));
                        if (botao) {
                            HabilitaCampo(botao);
                        }
                        break;
                    case 'erro':
                        RetornoErro(json.erro);
                        if (botao) {
                            HabilitaCampo(botao);
                        }
                        break;
                    case 'msg_erro':
                        var erro = dadosErrados(json.msg_erro);
                        alert(erro.join("\n\t- "));
                        if (botao) {
                            HabilitaCampo(botao);
                        }
                        break;
                    case 'parametro_errado':
                        alert('ERRO: Parametro nao encontrado no Controler, contate o Administrador do sistema sobre este erro');
                        if (botao) {
                            HabilitaCampo(botao);
                        }
                        break;
                }
            },
            onStateChange: function(req, id) {
                if (divAguardando) {
                    if (req.readyState != 4) {
                        //GetId(divAguardando).innerHTML = '<img src="imagem/aguardando.gif">';
                        GetId(divAguardando).setAttribute("class", "spanAguardando");
                    } else {
                        //GetId(divAguardando).innerHTML = '';
                        GetId(divAguardando).removeAttribute("class");
                    }
                }
            },
            onError: function(rq, id, msg) {
                alert('Ocorreu um problema tente novamente em instantes, caso persista contate o administrador.');
            }
        });
    },
    PesquisaGeral : function( form, pag, div, popup, tamanhoPopup, camada ){
        if( !Valida.ValidaForm( form ) ){
            return false;
        }
        if( popup ){
            if( camada == '' ){
                camada = 0;
            }
            CriaPopup( pag + PegaDados.Formulario( form ), tamanhoPopup, camada );
        } else {
            Load( pag + PegaDados.Formulario( form ), div );
        }
    }
};
function RetornoErro(erro, linguagem) {
    switch (linguagem) {
        case 'en':
            switch (erro) {
                case 'inserir':
                    alert("ERROR: There was a problem when entering the data. \nContact the system administrator");
                    break;
                case 'excluir':
                    alert('For some reason the data cant be excluded!');
                    break;
                case 'editar':
                    alert("There is some problem editing the data.\n Contact the system administrador");
                    break;
                case 'alterar_valor':
                    alert("There is some problem changing the value.\nContact the system administrator");
                    break;
                case 'class_nao_definida':
                    alert('Class was not defined, contact the system administrator');
                    break;
                case 'enviar_email':
                    alert('There was a problem sending email.\nContact the system administrator');
                    break;
                default :
                    Redirect('erro.php?erro=' + erro);
                    break;
            }
            break;
        case 'es':
            switch (erro) {
                case 'inserir':
                    alert("ERROR: Hubo un problema al introducir los datos.\nContactar con el Administrador del sistema");
                    break;
                case 'excluir':
                    alert('Por alguna razón, el canto de datos se excluyen!');
                    break;
                case 'editar':
                    alert("Hay algunos problemas de edición de los datos.\nContactar con el Administrador del sistema");
                    break;
                case 'alterar_valor':
                    alert("Hay algún problema cambiando el valor.\nContactar con el Administrador del sistema");
                    break;
                case 'class_nao_definida':
                    alert('Clase no se definió, contactar con el Administrador del sistema');
                    break;
                case 'enviar_email':
                    alert('Hubo un problema al enviar el correo.\nContactar con el Administrador del sistema');
                    break;
                default :
                    Redirect('erro.php?erro=' + erro);
                    break;
            }
            break;
        default:
            switch (erro) {
                case 'inserir':
                    alert("ERRO: Ocorreu algum problema na hora de inserir os dados.\nContate o administrador");
                    break;
                case 'excluir':
                    alert('Por algum motivo este registro nao pode ser excluido!');
                    break;
                case 'editar':
                    alert("Ocorreu algum problema na hora de alterar os dados.\nContate o administrador");
                    break;
                case 'alterar_valor':
                    alert("Ocorreu algum problema na hora de alterar o valor.\nContate o administrador");
                    break;
                case 'class_nao_definida':
                    alert('Classe nao foi definida, contate o administrador do sistema');
                    break;
                case 'enviar_email':
                    alert('Ocorreu algum problema ao enviar o email.\nContate o administrador');
                    break;
                case 'erroUsuario':
                    alert('Talvez esse usuário já exista em nosso sistema.\nPor favor, tente outro usuário.');
                    break;
                default :
                    Redirect('erro.php?erro=' + erro);
                    break;
            }
            break;
    }
}
function RetornoPositivo(retorno, linguagem) {
    switch (linguagem) {
        case 'en':
            switch (retorno) {
                case 'inserir':
                    alert("Data successfully registered!");
                    break;
                case 'enviado':
                    alert("Request successfully sent!");
                    break;
                case 'excluir':
                    alert("Data successfully excluded!");
                    break;
                case 'editar':
                    alert("Data successfully edited!");
                    break;
                case 'alterar_valor':
                    alert("Value successfully edited !");
                    break;
                case 'email_enviado':
                    alert("Email successfully sent!");
                    break;
            }
            break;
        case 'es':
            switch (retorno) {
                case 'inserir':
                    alert("Los datos introducidos con éxito!");
                    break;
                case 'enviado':
                    alert("Solicitud se ha enviado con éxito!");
                    break;
                case 'excluir':
                    alert("Datos éxito excluidos!");
                    break;
                case 'editar':
                    alert("Los datos modificados con éxito!");
                    break;
                case 'alterar_valor':
                    alert("Cambiado correctamente el valor!");
                    break;
                case 'email_enviado':
                    alert("Correo electrónico enviado con éxito!");
                    break;
            }
            break;
        default:
            switch (retorno) {
                case 'inserir':
                    alert("Dados inseridos com sucesso!");
                    break;
                case 'enviado':
                    alert("Pedido enviado com sucesso! Em breve nossa equipe entrará em contato.");
                    break;
                case 'excluir':
                    alert("Dados excluidos com sucesso!");
                    break;
                case 'editar':
                    alert("Dados alterados com sucesso!");
                    break;
                case 'alterar_valor':
                    alert("Alterado o valor com sucesso!");
                    break;
                case 'email_enviado':
                    alert("Email enviado com sucesso!");
                    break;
            }
            break;
    }
}
function dadosErrados(json) {
    var erro = [];
    for (var i in json) {
        erro[erro.length] = json[i];
    }
    return erro;
}
function ExcluirPadrao(botao, form, id, pagina, div, lingua) {
    try {
        DesabilitaCampo(botao);
        if (!confirm("Deseja realmente excluir?")) {
            HabilitaCampo(botao);
            return false;
        }
        var Retorno = function() {
            var ajax = Ajax.request;
            if (ajax.readyState == 4) {
                GetId('Aguardando' + botao.id).innerHTML = '';
                if (ajax.status == 200) {
                    var json = eval('(' + ajax.responseText + ')');
                    switch (json.resultado) {
                        case 'sim':
                            try {
                                RetornoPositivo('excluir', GetId(form).getAttribute('lingua_form').toLowerCase());
                            } catch (e) {
                                RetornoPositivo('excluir')
                            }
                            HabilitaCampo(botao);
                            if (pagina && div)
                                Load(pagina, div);
                            break;
                        case 'dados_errados':
                            var erro = dadosErrados(json.dados_errados);
                            var msg = eval("Valida.Msg" + Valida.LinguaForm(form).toUpperCase());
                            alert(msg + " \n\t- " + erro.join("\n\t- "));
                            HabilitaCampo(botao);
                            break;
                        case 'erro':
                            RetornoErro(json.erro);
                            HabilitaCampo(botao);
                            break;
                    }
                }
            } else if (ajax.readyState != 4) {
                GetId('Aguardando' + botao.id).innerHTML = '<img src="imagem/aguardando.gif">';
            }
        };
        var valores = "acao=ExcluirPadrao&id_form=" + id + "&form=" + form;
        Ajax.Solicitacao("acao.php", valores, Retorno);
        Ajax.Solicitar();
    } catch (e) {
    }
}
function InserirPadrao(botao, form, pagina, div, camada) {
    try {
        if (!camada)
            camada = 1;
        DesabilitaCampo(botao);
        if (!Valida.ValidaForm(form)) {
            HabilitaCampo(botao);
            return false;
        }
        var Retorno = function() {
            var ajax = Ajax.request;
            if (ajax.readyState == 4) {
                GetId('aguardando').innerHTML = '';
                if (ajax.status == 200) {
                    var json = eval('(' + ajax.responseText + ')');
                    switch (json.resultado) {
                        case 'sim':
                            try {
                                RetornoPositivo('inserir', GetId(form).getAttribute('lingua_form').toLowerCase());
                            } catch (e) {
                                RetornoPositivo('inserir');
                            }
                            HabilitaCampo(botao);
                            RemovePopup(camada);
                            if (pagina && div)
                                Load(pagina, div);
                            break;
                        case 'dados_errados':
                            var erro = dadosErrados(json.dados_errados);
                            var msg = eval("Valida.Msg" + Valida.LinguaForm(form).toUpperCase());
                            alert(msg + " \n\t- " + erro.join("\n\t- "));
                            HabilitaCampo(botao);
                            break;
                        case 'erro':
                            RetornoErro(json.erro);
                            HabilitaCampo(botao);
                            break;
                        case 'erroUsuario':
                            RetornoErro(json.erro);
                            HabilitaCampo(botao);
                            break;
                    }
                }
            } else if (ajax.readyState != 4) {
                GetId('aguardando').innerHTML = '<img src="imagem/aguardando.gif">';
            }
        };
        var valores = "acao=InserirPadrao&" + PegaDados.Formulario(form);
        Ajax.Solicitacao("acao.php", valores, Retorno);
        Ajax.Solicitar();
    } catch (e) {
        console.log(e);
    }
}
function EditarPadrao(botao, form, id, pagina, div, camada) {
    try {
        if (!camada)
            camada = 1;
        DesabilitaCampo(botao);
        if (!Valida.ValidaForm(form)) {
            HabilitaCampo(botao);
            return false;
        }
        var Retorno = function() {
            var ajax = Ajax.request;
            if (ajax.readyState == 4) {
                GetId('aguardando').innerHTML = '';
                if (ajax.status == 200) {
                    var json = eval('(' + ajax.responseText + ')');
                    switch (json.resultado) {
                        case 'sim':
                            try {
                                RetornoPositivo('editar', GetId(form).getAttribute('lingua_form').toLowerCase());
                            } catch (e) {
                                RetornoPositivo('editar');
                            }
                            HabilitaCampo(botao);
                            RemovePopup(camada);
                            if (pagina && div)
                                Load(pagina, div);
                            break;
                        case 'dados_errados':
                            var erro = dadosErrados(json.dados_errados);
                            var msg = eval("Valida.Msg" + Valida.LinguaForm(form).toUpperCase());
                            alert(msg + " \n\t- " + erro.join("\n\t- "));
                            HabilitaCampo(botao);
                            break;
                        case 'erro':
                            RetornoErro(json.erro);
                            HabilitaCampo(botao);
                            break;
                    }
                }
            } else if (ajax.readyState != 4) {
                GetId('aguardando').innerHTML = '<img src="imagem/aguardando.gif">';
            }
        };
        var valores = "acao=EditarPadrao&id_form=" + id + "&" + PegaDados.Formulario(form);
        Ajax.Solicitacao("acao.php", valores, Retorno);
        Ajax.Solicitar();
    } catch (e) {
        console.log(e);
    }
}
function AlteraValorPadrao(campo, form, pagina, div, tipo, naoDarAlerta) {
    DesabilitaCampo(campo);
    if(!Valida.ValidaCampoUnico(campo))
    {
        HabilitaCampo(campo);
        return false;
    }
    var Retorno = function() {
        var ajax = Ajax.request;
        if (ajax.readyState == 4) {
            if (ajax.status == 200) {
                var json = eval('(' + ajax.responseText + ')');
                switch (json.resultado) {
                    case 'sim':
                        if(naoDarAlerta)
                        {
                            campo.parentNode.innerHTML = campo.value;
                        }
                        else{
                            if (tipo == 'bool' || tipo == 'bool2') {
                                RetornoPositivo('excluir');
                            } else {
                                RetornoPositivo('alterar_valor');
                            }
                            HabilitaCampo(campo);
                        }
                        if (pagina && div)
                            Load(pagina, div);
                        break;
                    case 'dados_errados':
                        var erro = dadosErrados(json.dados_errados);
                        var msg = eval("Valida.Msg" + Valida.LinguaForm(form).toUpperCase());
                        alert(msg + " \n\t- " + erro.join("\n\t- "));
                        HabilitaCampo(campo);
                        if (pagina && div)
                            Load(pagina, div);
                        break;
                    case 'erro':
                        RetornoErro(json.erro);
                        HabilitaCampo(campo);
                        if (pagina && div)
                            Load(pagina, div);
                        break;
                }
            }
        }
    };
    var valor = '';
    if (tipo == 'bool') {
        valor = (campo.checked == true) ? 't' : 'f';
    } else {
        valor = campo.value;
    }
    var valores = "acao=AlteraValorPadrao&form=" + form + "&id_form=" + campo.id;
    var valida = campo.getAttribute('valida');
    valores += "&campo=" + campo.name + '&' + campo.name + '=' + valor + '&' + campo.name + "_Valida=" + encodeURIComponent(valida+ ',' + campo.type);;
    Ajax.Solicitacao("acao.php", valores, Retorno);
    Ajax.Solicitar();
}

function AtivoPadrao(campo, form, pagina, div, alerta, parametros) {
    DesabilitaCampo(campo);
    var ativo = (campo.checked == true) ? true : false;
    var URL = "acao.php";
    var valores = "acao=AtivoPadrao&form=" + form + "&id_form=" + campo.id + "&ativo=" + ativo + "&name=" + campo.name + "&parametros=" + parametros ;
    var ajax = CriaAjax();
    ajax.open('POST', URL, true);
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    ajax.onreadystatechange = function() {
        if (ajax.readyState == 4) {
            if (ajax.status == 200) {
                var json = eval('(' + ajax.responseText + ')');
                switch (json.resultado) {
                    case 'sim':
                        if (alerta)
                            alert('Ativo alterado com sucesso')
                        HabilitaCampo(campo);
                        if (pagina && div)
                            Load(pagina, div);
                        break;
                    case 'dados_errados':
                        var erro = dadosErrados(json.dados_errados);
                        var msg = eval("Valida.Msg" + Valida.LinguaForm(form).toUpperCase());
                        alert(msg + " \n\t- " + erro.join("\n\t- "));
                        HabilitaCampo(campo);
                        if (pagina && div)
                            Load(pagina, div);
                        break;
                    case 'erro':
                        alert('Ocorreu um erro ao alterar o ativo.\nContate o administrador');
                        HabilitaCampo(campo);
                        if (pagina && div)
                            Load(pagina, div);
                        break;
                }
            }
        }
    };
    ajax.send(valores);
}
var oldLink = null;
function setActiveStyleSheet(link, title) {
    var i, a, main;
    for (i = 0; (a = document.getElementsByTagName("link")[i]); i++) {
        if (a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title")) {
            a.disabled = true;
            if (a.getAttribute("title") == title)
                a.disabled = false;
        }
    }
    if (oldLink)
        oldLink.style.fontWeight = 'normal';
    oldLink = link;
    link.style.fontWeight = 'bold';
    return false;
}
function selected(cal, date) {
    cal.sel.value = date;
    if (cal.dateClicked && (cal.sel.id == "sel1" || cal.sel.id == "sel3"))
        cal.callCloseHandler();
}
function closeHandler(cal) {
    cal.hide();
    _dynarch_popupCalendar = null;
}
function showCalendar(id, format, showsTime, showsOtherMonths) {
    var el = document.getElementById(id);
    if (_dynarch_popupCalendar != null) {
        _dynarch_popupCalendar.hide();
    } else {
        var cal = new Calendar(1, null, selected, closeHandler);
        if (typeof showsTime == "string") {
            cal.showsTime = true;
            cal.time24 = (showsTime == "24");
        }
        if (showsOtherMonths) {
            cal.showsOtherMonths = true;
        }
        _dynarch_popupCalendar = cal;
        cal.setRange(1900, 2070);
        cal.create();
    }
    _dynarch_popupCalendar.setDateFormat(format);
    _dynarch_popupCalendar.parseDate(el.value);
    _dynarch_popupCalendar.sel = el;
    _dynarch_popupCalendar.showAtElement(el.nextSibling, "Br");
    return false;
}
var MINUTE = 60 * 1000;
var HOUR = 60 * MINUTE;
var DAY = 24 * HOUR;
var WEEK = 7 * DAY;
function isDisabled(date) {
    var today = new Date();
    return (Math.abs(date.getTime() - today.getTime()) / DAY) > 10;
}
function flatSelected(cal, date) {
    var el = document.getElementById("preview");
    el.innerHTML = date;
}
function showFlatCalendar() {
    var parent = document.getElementById("display");
    var cal = new Calendar(0, null, flatSelected);
    cal.weekNumbers = false;
    cal.setDisabledHandler(isDisabled);
    cal.setDateFormat("%A, %B %e");
    cal.create(parent);
    cal.show();
}
var turnOffYearSpan = false;
var weekStartsOnSunday = false;
var showWeekNumber = true;
var languageCode = 'pt-br';
var calendar_display_time = true;
var todayStringFormat = '[todayString] [UCFdayString]. [day]. [monthString] [year]';
var pathToImages = 'imagem/calendario/';
var speedOfSelectBoxSliding = 200;
var intervalSelectBox_minutes = 1;
var calendar_offsetTop = 0;
var calendar_offsetLeft = 0;
var calendarDiv = false;
var MSIE = false;
var Opera = false;
if (navigator.userAgent.indexOf('MSIE') >= 0 && navigator.userAgent.indexOf('Opera') < 0)
    MSIE = true;
if (navigator.userAgent.indexOf('Opera') >= 0)
    Opera = true;
switch (languageCode) {
    case "pt-br":
        var monthArray = ['Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        var monthArrayShort = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        var dayArray = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S&aacute;b', 'Dom'];
        var weekString = 'Sem.';
        var todayString = 'Hoje &eacute;';
        break;
}
if (weekStartsOnSunday) {
    var tempDayName = dayArray[6];
    for (var theIx = 6; theIx > 0; theIx--) {
        dayArray[theIx] = dayArray[theIx - 1];
    }
    dayArray[0] = tempDayName;
}
var daysInMonthArray = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
var currentMonth;
var currentYear;
var currentHour;
var currentMinute;
var calendarContentDiv;
var returnDateTo;
var returnFormat;
var activeSelectBoxMonth;
var activeSelectBoxYear;
var activeSelectBoxHour;
var activeSelectBoxMinute;
var iframeObj = false;
var iframeObj2 = false;
function EIS_FIX_EI1(where2fixit)
{
    if (!iframeObj2)
        return;
    iframeObj2.style.display = 'block';
    iframeObj2.style.height = document.getElementById(where2fixit).offsetHeight + 1;
    iframeObj2.style.width = document.getElementById(where2fixit).offsetWidth;
    iframeObj2.style.left = getleftPos(document.getElementById(where2fixit)) + 1 - calendar_offsetLeft;
    iframeObj2.style.top = getTopPos(document.getElementById(where2fixit)) - document.getElementById(where2fixit).offsetHeight - calendar_offsetTop;
}
function EIS_Hide_Frame()
{
    if (iframeObj2)
        iframeObj2.style.display = 'none';
}
var returnDateToYear;
var returnDateToMonth;
var returnDateToDay;
var returnDateToHour;
var returnDateToMinute;
var inputYear;
var inputMonth;
var inputDay;
var inputHour;
var inputMinute;
var calendarDisplayTime = false;
var selectBoxHighlightColor = '#D60808';
var selectBoxRolloverBgColor = '#E2EBED';
var selectBoxMovementInProgress = false;
var activeSelectBox = false;
function cancelCalendarEvent()
{
    return false;
}
function isLeapYear(inputYear)
{
    if (inputYear % 400 == 0 || (inputYear % 4 == 0 && inputYear % 100 != 0))
        return true;
    return false;
}
var activeSelectBoxMonth = false;
var activeSelectBoxDirection = false;
function highlightMonthYear()
{
    if (activeSelectBoxMonth)
        activeSelectBoxMonth.className = '';
    activeSelectBox = this;
    if (this.className == 'monthYearActive') {
        this.className = '';
    } else {
        this.className = 'monthYearActive';
        activeSelectBoxMonth = this;
    }
    if (this.innerHTML.indexOf('-') >= 0 || this.innerHTML.indexOf('+') >= 0) {
        if (this.className == 'monthYearActive')
            selectBoxMovementInProgress = true;
        else
            selectBoxMovementInProgress = false;
        if (this.innerHTML.indexOf('-') >= 0)
            activeSelectBoxDirection = -1;
        else
            activeSelectBoxDirection = 1;
    } else
        selectBoxMovementInProgress = false;
}
function showMonthDropDown()
{
    if (document.getElementById('monthDropDown').style.display == 'block') {
        document.getElementById('monthDropDown').style.display = 'none';
        EIS_Hide_Frame();
    } else {
        document.getElementById('monthDropDown').style.display = 'block';
        document.getElementById('yearDropDown').style.display = 'none';
        document.getElementById('hourDropDown').style.display = 'none';
        document.getElementById('minuteDropDown').style.display = 'none';
        if (MSIE)
        {
            EIS_FIX_EI1('monthDropDown')
        }
    }
}
function showYearDropDown()
{
    if (document.getElementById('yearDropDown').style.display == 'block') {
        document.getElementById('yearDropDown').style.display = 'none';
        EIS_Hide_Frame();
    } else {
        document.getElementById('yearDropDown').style.display = 'block';
        document.getElementById('monthDropDown').style.display = 'none';
        document.getElementById('hourDropDown').style.display = 'none';
        document.getElementById('minuteDropDown').style.display = 'none';
        if (MSIE)
        {
            EIS_FIX_EI1('yearDropDown')
        }
    }
}
function showHourDropDown()
{
    if (document.getElementById('hourDropDown').style.display == 'block') {
        document.getElementById('hourDropDown').style.display = 'none';
        EIS_Hide_Frame();
    } else {
        document.getElementById('hourDropDown').style.display = 'block';
        document.getElementById('monthDropDown').style.display = 'none';
        document.getElementById('yearDropDown').style.display = 'none';
        document.getElementById('minuteDropDown').style.display = 'none';
        if (MSIE)
        {
            EIS_FIX_EI1('hourDropDown')
        }
    }
}
function showMinuteDropDown()
{
    if (document.getElementById('minuteDropDown').style.display == 'block') {
        document.getElementById('minuteDropDown').style.display = 'none';
        EIS_Hide_Frame();
    } else {
        document.getElementById('minuteDropDown').style.display = 'block';
        document.getElementById('monthDropDown').style.display = 'none';
        document.getElementById('yearDropDown').style.display = 'none';
        document.getElementById('hourDropDown').style.display = 'none';
        if (MSIE)
        {
            EIS_FIX_EI1('minuteDropDown')
        }
    }
}
function selectMonth()
{
    document.getElementById('calendar_month_txt').innerHTML = this.innerHTML
    currentMonth = this.id.replace(/[^\d]/g, '');
    document.getElementById('monthDropDown').style.display = 'none';
    EIS_Hide_Frame();
    for (var no = 0; no < monthArray.length; no++) {
        document.getElementById('monthDiv_' + no).style.color = '';
    }
    this.style.color = selectBoxHighlightColor;
    activeSelectBoxMonth = this;
    writeCalendarContent();
}
function selectHour()
{
    document.getElementById('calendar_hour_txt').innerHTML = this.innerHTML
    currentHour = this.innerHTML.replace(/[^\d]/g, '');
    document.getElementById('hourDropDown').style.display = 'none';
    EIS_Hide_Frame();
    if (activeSelectBoxHour) {
        activeSelectBoxHour.style.color = '';
    }
    activeSelectBoxHour = this;
    this.style.color = selectBoxHighlightColor;
}
function selectMinute()
{
    document.getElementById('calendar_minute_txt').innerHTML = this.innerHTML
    currentMinute = this.innerHTML.replace(/[^\d]/g, '');
    document.getElementById('minuteDropDown').style.display = 'none';
    EIS_Hide_Frame();
    if (activeSelectBoxMinute) {
        activeSelectBoxMinute.style.color = '';
    }
    activeSelectBoxMinute = this;
    this.style.color = selectBoxHighlightColor;
}
function selectYear()
{
    document.getElementById('calendar_year_txt').innerHTML = this.innerHTML
    currentYear = this.innerHTML.replace(/[^\d]/g, '');
    document.getElementById('yearDropDown').style.display = 'none';
    EIS_Hide_Frame();
    if (activeSelectBoxYear) {
        activeSelectBoxYear.style.color = '';
    }
    activeSelectBoxYear = this;
    this.style.color = selectBoxHighlightColor;
    writeCalendarContent();
}
function switchMonth()
{
    if (this.src.indexOf('left') >= 0) {
        currentMonth = currentMonth - 1;
        ;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear = currentYear - 1;
        }
    } else {
        currentMonth = currentMonth + 1;
        ;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear = currentYear / 1 + 1;
        }
    }
    writeCalendarContent();
}
function createMonthDiv() {
    var div = document.createElement('DIV');
    div.className = 'monthYearPicker';
    div.id = 'monthPicker';
    for (var no = 0; no < monthArray.length; no++) {
        var subDiv = document.createElement('DIV');
        subDiv.innerHTML = monthArray[no];
        subDiv.onmouseover = highlightMonthYear;
        subDiv.onmouseout = highlightMonthYear;
        subDiv.onclick = selectMonth;
        subDiv.id = 'monthDiv_' + no;
        subDiv.style.width = '56px';
        subDiv.onselectstart = cancelCalendarEvent;
        div.appendChild(subDiv);
        if (currentMonth && currentMonth == no) {
            subDiv.style.color = selectBoxHighlightColor;
            activeSelectBoxMonth = subDiv;
        }
    }
    return div;
}
function changeSelectBoxYear(e, inputObj)
{
    if (!inputObj)
        inputObj = this;
    var yearItems = inputObj.parentNode.getElementsByTagName('DIV');
    if (inputObj.innerHTML.indexOf('-') >= 0) {
        var startYear = yearItems[1].innerHTML / 1 - 1;
        if (activeSelectBoxYear) {
            activeSelectBoxYear.style.color = '';
        }
    } else {
        var startYear = yearItems[1].innerHTML / 1 + 1;
        if (activeSelectBoxYear) {
            activeSelectBoxYear.style.color = '';
        }
    }
    for (var no = 1; no < yearItems.length - 1; no++) {
        yearItems[no].innerHTML = startYear + no - 1;
        yearItems[no].id = 'yearDiv' + (startYear / 1 + no / 1 - 1);
    }
    if (activeSelectBoxYear) {
        activeSelectBoxYear.style.color = '';
        if (document.getElementById('yearDiv' + currentYear)) {
            activeSelectBoxYear = document.getElementById('yearDiv' + currentYear);
            activeSelectBoxYear.style.color = selectBoxHighlightColor;
            ;
        }
    }
}
function changeSelectBoxHour(e, inputObj)
{
    if (!inputObj)
        inputObj = this;
    var hourItems = inputObj.parentNode.getElementsByTagName('DIV');
    if (inputObj.innerHTML.indexOf('-') >= 0) {
        var startHour = hourItems[1].innerHTML / 1 - 1;
        if (startHour < 0)
            startHour = 0;
        if (activeSelectBoxHour) {
            activeSelectBoxHour.style.color = '';
        }
    } else {
        var startHour = hourItems[1].innerHTML / 1 + 1;
        if (startHour > 14)
            startHour = 14;
        if (activeSelectBoxHour) {
            activeSelectBoxHour.style.color = '';
        }
    }
    var prefix = '';
    for (var no = 1; no < hourItems.length - 1; no++) {
        if ((startHour / 1 + no / 1) < 11)
            prefix = '0';
        else
            prefix = '';
        hourItems[no].innerHTML = prefix + (startHour + no - 1);
        hourItems[no].id = 'hourDiv' + (startHour / 1 + no / 1 - 1);
    }
    if (activeSelectBoxHour) {
        activeSelectBoxHour.style.color = '';
        if (document.getElementById('hourDiv' + currentHour)) {
            activeSelectBoxHour = document.getElementById('hourDiv' + currentHour);
            activeSelectBoxHour.style.color = selectBoxHighlightColor;
            ;
        }
    }
}
function updateYearDiv()
{
    var yearSpan = 5;
    if (turnOffYearSpan) {
        yearSpan = 0;
    }
    var div = document.getElementById('yearDropDown');
    var yearItems = div.getElementsByTagName('DIV');
    for (var no = 1; no < yearItems.length - 1; no++) {
        yearItems[no].innerHTML = currentYear / 1 - yearSpan + no;
        if (currentYear == (currentYear / 1 - yearSpan + no)) {
            yearItems[no].style.color = selectBoxHighlightColor;
            activeSelectBoxYear = yearItems[no];
        } else {
            yearItems[no].style.color = '';
        }
    }
}
function updateMonthDiv()
{
    for (no = 0; no < 12; no++) {
        document.getElementById('monthDiv_' + no).style.color = '';
    }
    document.getElementById('monthDiv_' + currentMonth).style.color = selectBoxHighlightColor;
    activeSelectBoxMonth = document.getElementById('monthDiv_' + currentMonth);
}
function updateHourDiv()
{
    var div = document.getElementById('hourDropDown');
    var hourItems = div.getElementsByTagName('DIV');
    var addHours = 0;
    if ((currentHour / 1 - 6 + 1) < 0) {
        addHours = (currentHour / 1 - 6 + 1) * -1;
    }
    for (var no = 1; no < hourItems.length - 1; no++) {
        var prefix = '';
        if ((currentHour / 1 - 6 + no + addHours) < 10)
            prefix = '0';
        hourItems[no].innerHTML = prefix + (currentHour / 1 - 6 + no + addHours);
        if (currentHour == (currentHour / 1 - 6 + no)) {
            hourItems[no].style.color = selectBoxHighlightColor;
            activeSelectBoxHour = hourItems[no];
        } else {
            hourItems[no].style.color = '';
        }
    }
}
function updateMinuteDiv()
{
    for (no = 0; no < 60; no += intervalSelectBox_minutes) {
        var prefix = '';
        if (no < 10)
            prefix = '0';
        document.getElementById('minuteDiv_' + prefix + no).style.color = '';
    }
    if (document.getElementById('minuteDiv_' + currentMinute)) {
        document.getElementById('minuteDiv_' + currentMinute).style.color = selectBoxHighlightColor;
        activeSelectBoxMinute = document.getElementById('minuteDiv_' + currentMinute);
    }
}
function createYearDiv()
{
    if (!document.getElementById('yearDropDown')) {
        var div = document.createElement('DIV');
        div.className = 'monthYearPicker';
    } else {
        var div = document.getElementById('yearDropDown');
        var subDivs = div.getElementsByTagName('DIV');
        for (var no = 0; no < subDivs.length; no++) {
            subDivs[no].parentNode.removeChild(subDivs[no]);
        }
    }
    var d = new Date();
    if (currentYear) {
        d.setFullYear(currentYear);
    }
    var startYear = d.getFullYear() / 1 - 5;
    var yearSpan = 10;
    if (!turnOffYearSpan) {
        var subDiv = document.createElement('DIV');
        subDiv.innerHTML = '&nbsp;&nbsp;- ';
        subDiv.onclick = changeSelectBoxYear;
        subDiv.onmouseover = highlightMonthYear;
        subDiv.onmouseout = function() {
            selectBoxMovementInProgress = false;
        };
        subDiv.onselectstart = cancelCalendarEvent;
        div.appendChild(subDiv);
    } else {
        startYear = d.getFullYear() / 1 - 0;
        yearSpan = 2;
    }
    for (var no = startYear; no < (startYear + yearSpan); no++) {
        var subDiv = document.createElement('DIV');
        subDiv.innerHTML = no;
        subDiv.onmouseover = highlightMonthYear;
        subDiv.onmouseout = highlightMonthYear;
        subDiv.onclick = selectYear;
        subDiv.id = 'yearDiv' + no;
        subDiv.onselectstart = cancelCalendarEvent;
        div.appendChild(subDiv);
        if (currentYear && currentYear == no) {
            subDiv.style.color = selectBoxHighlightColor;
            activeSelectBoxYear = subDiv;
        }
    }
    if (!turnOffYearSpan) {
        var subDiv = document.createElement('DIV');
        subDiv.innerHTML = '&nbsp;&nbsp;+ ';
        subDiv.onclick = changeSelectBoxYear;
        subDiv.onmouseover = highlightMonthYear;
        subDiv.onmouseout = function() {
            selectBoxMovementInProgress = false;
        };
        subDiv.onselectstart = cancelCalendarEvent;
        div.appendChild(subDiv);
    }
    return div;
}
function slideCalendarSelectBox()
{
    if (selectBoxMovementInProgress) {
        if (activeSelectBox.parentNode.id == 'hourDropDown') {
            changeSelectBoxHour(false, activeSelectBox);
        }
        if (activeSelectBox.parentNode.id == 'yearDropDown') {
            changeSelectBoxYear(false, activeSelectBox);
        }
    }
    setTimeout('slideCalendarSelectBox()', speedOfSelectBoxSliding);
}
function createHourDiv()
{
    if (!document.getElementById('hourDropDown')) {
        var div = document.createElement('DIV');
        div.className = 'monthYearPicker';
    } else {
        var div = document.getElementById('hourDropDown');
        var subDivs = div.getElementsByTagName('DIV');
        for (var no = 0; no < subDivs.length; no++) {
            subDivs[no].parentNode.removeChild(subDivs[no]);
        }
    }
    if (!currentHour)
        currentHour = 0;
    var startHour = currentHour / 1;
    if (startHour > 14)
        startHour = 14;
    var subDiv = document.createElement('DIV');
    subDiv.innerHTML = '&nbsp;&nbsp;- ';
    subDiv.onclick = changeSelectBoxHour;
    subDiv.onmouseover = highlightMonthYear;
    subDiv.onmouseout = function() {
        selectBoxMovementInProgress = false;
    };
    subDiv.onselectstart = cancelCalendarEvent;
    div.appendChild(subDiv);
    for (var no = startHour; no < startHour + 10; no++) {
        var prefix = '';
        if (no / 1 < 10)
            prefix = '0';
        var subDiv = document.createElement('DIV');
        subDiv.innerHTML = prefix + no;
        subDiv.onmouseover = highlightMonthYear;
        subDiv.onmouseout = highlightMonthYear;
        subDiv.onclick = selectHour;
        subDiv.id = 'hourDiv' + no;
        subDiv.onselectstart = cancelCalendarEvent;
        div.appendChild(subDiv);
        if (currentYear && currentYear == no) {
            subDiv.style.color = selectBoxHighlightColor;
            activeSelectBoxYear = subDiv;
        }
    }
    var subDiv = document.createElement('DIV');
    subDiv.innerHTML = '&nbsp;&nbsp;+ ';
    subDiv.onclick = changeSelectBoxHour;
    subDiv.onmouseover = highlightMonthYear;
    subDiv.onmouseout = function() {
        selectBoxMovementInProgress = false;
    };
    subDiv.onselectstart = cancelCalendarEvent;
    div.appendChild(subDiv);
    return div;
}
function createMinuteDiv()
{
    if (!document.getElementById('minuteDropDown')) {
        var div = document.createElement('DIV');
        div.className = 'monthYearPicker';
    } else {
        var div = document.getElementById('minuteDropDown');
        var subDivs = div.getElementsByTagName('DIV');
        for (var no = 0; no < subDivs.length; no++) {
            subDivs[no].parentNode.removeChild(subDivs[no]);
        }
    }
    var startMinute = 0;
    var prefix = '';
    for (var no = startMinute; no < 60; no += intervalSelectBox_minutes) {
        if (no < 10)
            prefix = '0';
        else
            prefix = '';
        var subDiv = document.createElement('DIV');
        subDiv.innerHTML = prefix + no;
        subDiv.onmouseover = highlightMonthYear;
        subDiv.onmouseout = highlightMonthYear;
        subDiv.onclick = selectMinute;
        subDiv.id = 'minuteDiv_' + prefix + no;
        subDiv.onselectstart = cancelCalendarEvent;
        div.appendChild(subDiv);
        if (currentYear && currentYear == no) {
            subDiv.style.color = selectBoxHighlightColor;
            activeSelectBoxYear = subDiv;
        }
    }
    return div;
}
function highlightSelect()
{
    if (this.className == 'selectBoxTime') {
        this.className = 'selectBoxTimeOver';
        this.getElementsByTagName('IMG')[0].src = pathToImages + 'down_time_over.gif';
    } else if (this.className == 'selectBoxTimeOver') {
        this.className = 'selectBoxTime';
        this.getElementsByTagName('IMG')[0].src = pathToImages + 'down_time.gif';
    }
    if (this.className == 'selectBox') {
        this.className = 'selectBoxOver';
        this.getElementsByTagName('IMG')[0].src = pathToImages + 'down_over.gif';
    } else if (this.className == 'selectBoxOver') {
        this.className = 'selectBox';
        this.getElementsByTagName('IMG')[0].src = pathToImages + 'down.gif';
    }
}
function highlightArrow()
{
    if (this.src.indexOf('over') >= 0) {
        if (this.src.indexOf('left') >= 0)
            this.src = pathToImages + 'left.gif';
        if (this.src.indexOf('right') >= 0)
            this.src = pathToImages + 'right.gif';
    } else {
        if (this.src.indexOf('left') >= 0)
            this.src = pathToImages + 'left_over.gif';
        if (this.src.indexOf('right') >= 0)
            this.src = pathToImages + 'right_over.gif';
    }
}
function highlightClose()
{
    if (this.src.indexOf('over') >= 0) {
        this.src = pathToImages + 'close.gif';
    } else {
        this.src = pathToImages + 'close_over.gif';
    }
}
function closeCalendar() {
    document.getElementById('yearDropDown').style.display = 'none';
    document.getElementById('monthDropDown').style.display = 'none';
    document.getElementById('hourDropDown').style.display = 'none';
    document.getElementById('minuteDropDown').style.display = 'none';
    calendarDiv.style.display = 'none';
    if (iframeObj) {
        iframeObj.style.display = 'none';
        EIS_Hide_Frame();
    }
    if (activeSelectBoxMonth)
        activeSelectBoxMonth.className = '';
    if (activeSelectBoxYear)
        activeSelectBoxYear.className = '';
}
function writeTopBar()
{
    var topBar = document.createElement('DIV');
    topBar.className = 'topBar';
    topBar.id = 'topBar';
    calendarDiv.appendChild(topBar);
    var leftDiv = document.createElement('DIV');
    leftDiv.style.marginRight = '1px';
    var img = document.createElement('IMG');
    img.src = pathToImages + 'left.gif';
    img.onmouseover = highlightArrow;
    img.onclick = switchMonth;
    img.onmouseout = highlightArrow;
    leftDiv.appendChild(img);
    topBar.appendChild(leftDiv);
    if (Opera)
        leftDiv.style.width = '16px';
    var rightDiv = document.createElement('DIV');
    rightDiv.style.marginRight = '1px';
    var img = document.createElement('IMG');
    img.src = pathToImages + 'right.gif';
    img.onclick = switchMonth;
    img.onmouseover = highlightArrow;
    img.onmouseout = highlightArrow;
    rightDiv.appendChild(img);
    if (Opera)
        rightDiv.style.width = '16px';
    topBar.appendChild(rightDiv);
    var monthDiv = document.createElement('DIV');
    monthDiv.id = 'monthSelect';
    monthDiv.onmouseover = highlightSelect;
    monthDiv.onmouseout = highlightSelect;
    monthDiv.onclick = showMonthDropDown;
    var span = document.createElement('SPAN');
    span.innerHTML = monthArray[currentMonth];
    span.id = 'calendar_month_txt';
    monthDiv.appendChild(span);
    var img = document.createElement('IMG');
    img.src = pathToImages + 'down.gif';
    img.style.position = 'absolute';
    img.style.right = '0px';
    monthDiv.appendChild(img);
    monthDiv.className = 'selectBox';
    if (Opera) {
        img.style.cssText = 'float:right;position:relative';
        img.style.position = 'relative';
        img.style.styleFloat = 'right';
    }
    topBar.appendChild(monthDiv);
    var monthPicker = createMonthDiv();
    monthPicker.style.left = '37px';
    monthPicker.style.top = monthDiv.offsetTop + monthDiv.offsetHeight + 1 + 'px';
    monthPicker.style.width = '105px';
    monthPicker.id = 'monthDropDown';
    calendarDiv.appendChild(monthPicker);
    var yearDiv = document.createElement('DIV');
    yearDiv.onmouseover = highlightSelect;
    yearDiv.onmouseout = highlightSelect;
    yearDiv.onclick = showYearDropDown;
    var span = document.createElement('SPAN');
    span.innerHTML = currentYear;
    span.id = 'calendar_year_txt';
    yearDiv.appendChild(span);
    topBar.appendChild(yearDiv);
    var img = document.createElement('IMG');
    img.src = pathToImages + 'down.gif';
    yearDiv.appendChild(img);
    yearDiv.className = 'selectBox';
    if (Opera) {
        yearDiv.style.width = '50px';
        img.style.cssText = 'float:right';
        img.style.position = 'relative';
        img.style.styleFloat = 'right';
    }
    var yearPicker = createYearDiv();
    yearPicker.style.left = '145px';
    yearPicker.style.top = monthDiv.offsetTop + monthDiv.offsetHeight + 1 + 'px';
    yearPicker.style.width = '45px';
    yearPicker.id = 'yearDropDown';
    calendarDiv.appendChild(yearPicker);
    var img = document.createElement('IMG');
    img.src = pathToImages + 'close.gif';
    img.style.styleFloat = 'right';
    img.onmouseover = highlightClose;
    img.onmouseout = highlightClose;
    img.onclick = closeCalendar;
    topBar.appendChild(img);
    if (!document.all) {
        img.style.position = 'absolute';
        img.style.right = '2px';
    }
}
function writeCalendarContent()
{
    var calendarContentDivExists = true;
    if (!calendarContentDiv) {
        calendarContentDiv = document.createElement('DIV');
        calendarDiv.appendChild(calendarContentDiv);
        calendarContentDivExists = false;
    }
    currentMonth = currentMonth / 1;
    var d = new Date();
    d.setFullYear(currentYear);
    d.setDate(1);
    d.setMonth(currentMonth);
    var dayStartOfMonth = d.getDay();
    if (!weekStartsOnSunday) {
        if (dayStartOfMonth == 0)
            dayStartOfMonth = 7;
        dayStartOfMonth--;
    }
    document.getElementById('calendar_year_txt').innerHTML = currentYear;
    document.getElementById('calendar_month_txt').innerHTML = monthArray[currentMonth];
    document.getElementById('calendar_hour_txt').innerHTML = currentHour;
    document.getElementById('calendar_minute_txt').innerHTML = currentMinute;
    var existingTable = calendarContentDiv.getElementsByTagName('TABLE');
    if (existingTable.length > 0) {
        calendarContentDiv.removeChild(existingTable[0]);
    }
    var calTable = document.createElement('TABLE');
    calTable.width = '100%';
    calTable.cellSpacing = '0';
    calendarContentDiv.appendChild(calTable);
    var calTBody = document.createElement('TBODY');
    calTable.appendChild(calTBody);
    var row = calTBody.insertRow(-1);
    row.className = 'calendar_week_row';
    if (showWeekNumber) {
        var cell = row.insertCell(-1);
        cell.innerHTML = weekString;
        cell.className = 'calendar_week_column';
        cell.style.backgroundColor = selectBoxRolloverBgColor;
    }
    for (var no = 0; no < dayArray.length; no++) {
        var cell = row.insertCell(-1);
        cell.innerHTML = dayArray[no];
    }
    var row = calTBody.insertRow(-1);
    if (showWeekNumber) {
        var cell = row.insertCell(-1);
        cell.className = 'calendar_week_column';
        cell.style.backgroundColor = selectBoxRolloverBgColor;
        var week = getWeek(currentYear, currentMonth, 1);
        cell.innerHTML = week;
    }
    for (var no = 0; no < dayStartOfMonth; no++) {
        var cell = row.insertCell(-1);
        cell.innerHTML = '&nbsp;';
    }
    var colCounter = dayStartOfMonth;
    var daysInMonth = daysInMonthArray[currentMonth];
    if (daysInMonth == 28) {
        if (isLeapYear(currentYear))
            daysInMonth = 29;
    }
    for (var no = 1; no <= daysInMonth; no++) {
        d.setDate(no - 1);
        if (colCounter > 0 && colCounter % 7 == 0) {
            var row = calTBody.insertRow(-1);
            if (showWeekNumber) {
                var cell = row.insertCell(-1);
                cell.className = 'calendar_week_column';
                var week = getWeek(currentYear, currentMonth, no);
                cell.innerHTML = week;
                cell.style.backgroundColor = selectBoxRolloverBgColor;
            }
        }
        var cell = row.insertCell(-1);
        if (currentYear == inputYear && currentMonth == inputMonth && no == inputDay) {
            cell.className = 'activeDay';
        }
        cell.innerHTML = no;
        cell.onclick = pickDate;
        colCounter++;
    }
    if (!document.all) {
        if (calendarContentDiv.offsetHeight)
            document.getElementById('topBar').style.top = calendarContentDiv.offsetHeight + document.getElementById('timeBar').offsetHeight + document.getElementById('topBar').offsetHeight - 1 + 'px';
        else {
            document.getElementById('topBar').style.top = '';
            document.getElementById('topBar').style.bottom = '0px';
        }
    }
    if (iframeObj) {
        if (!calendarContentDivExists)
            setTimeout('resizeIframe()', 350);
        else
            setTimeout('resizeIframe()', 10);
    }
}
function resizeIframe()
{
    iframeObj.style.width = calendarDiv.offsetWidth + 'px';
    iframeObj.style.height = calendarDiv.offsetHeight + 'px';
}
function pickTodaysDate()
{
    var d = new Date();
    currentMonth = d.getMonth();
    currentYear = d.getFullYear();
    pickDate(false, d.getDate());
}
function pickDate(e, inputDay)
{
    var month = currentMonth / 1 + 1;
    if (month < 10)
        month = '0' + month;
    var day;
    if (!inputDay && this)
        day = this.innerHTML;
    else
        day = inputDay;
    if (day / 1 < 10)
        day = '0' + day;
    if (returnFormat) {
        returnFormat = returnFormat.replace('dd', day);
        returnFormat = returnFormat.replace('mm', month);
        returnFormat = returnFormat.replace('yyyy', currentYear);
        returnFormat = returnFormat.replace('hh', currentHour);
        returnFormat = returnFormat.replace('ii', currentMinute);
        returnFormat = returnFormat.replace('d', day / 1);
        returnFormat = returnFormat.replace('m', month / 1);
        returnDateTo.value = returnFormat;
        try {
            returnDateTo.onchange();
        } catch (e) {
        }
    } else {
        for (var no = 0; no < returnDateToYear.options.length; no++) {
            if (returnDateToYear.options[no].value == currentYear) {
                returnDateToYear.selectedIndex = no;
                break;
            }
        }
        for (var no = 0; no < returnDateToMonth.options.length; no++) {
            if (returnDateToMonth.options[no].value == parseInt(month)) {
                returnDateToMonth.selectedIndex = no;
                break;
            }
        }
        for (var no = 0; no < returnDateToDay.options.length; no++) {
            if (returnDateToDay.options[no].value == parseInt(day)) {
                returnDateToDay.selectedIndex = no;
                break;
            }
        }
        if (calendarDisplayTime) {
            for (var no = 0; no < returnDateToHour.options.length; no++) {
                if (returnDateToHour.options[no].value == parseInt(currentHour)) {
                    returnDateToHour.selectedIndex = no;
                    break;
                }
            }
            for (var no = 0; no < returnDateToMinute.options.length; no++) {
                if (returnDateToMinute.options[no].value == parseInt(currentMinute)) {
                    returnDateToMinute.selectedIndex = no;
                    break;
                }
            }
        }
    }
    closeCalendar();
}
function getWeek(year, month, day) {
    if (!weekStartsOnSunday) {
        day = (day / 1);
    } else {
        day = (day / 1) + 1;
    }
    year = year / 1;
    month = month / 1 + 1;
    var a = Math.floor((14 - (month)) / 12);
    var y = year + 4800 - a;
    var m = (month) + (12 * a) - 3;
    var jd = day + Math.floor(((153 * m) + 2) / 5) +
            (365 * y) + Math.floor(y / 4) - Math.floor(y / 100) +
            Math.floor(y / 400) - 32045;
    var d4 = (jd + 31741 - (jd % 7)) % 146097 % 36524 % 1461;
    var L = Math.floor(d4 / 1460);
    var d1 = ((d4 - L) % 365) + L;
    NumberOfWeek = Math.floor(d1 / 7) + 1;
    return NumberOfWeek;
}
function writeTimeBar()
{
    var timeBar = document.createElement('DIV');
    timeBar.id = 'timeBar';
    timeBar.className = 'timeBar';
    var subDiv = document.createElement('DIV');
    subDiv.innerHTML = 'Time:';
    var hourDiv = document.createElement('DIV');
    hourDiv.onmouseover = highlightSelect;
    hourDiv.onmouseout = highlightSelect;
    hourDiv.onclick = showHourDropDown;
    hourDiv.style.width = '30px';
    var span = document.createElement('SPAN');
    span.innerHTML = currentHour;
    span.id = 'calendar_hour_txt';
    hourDiv.appendChild(span);
    timeBar.appendChild(hourDiv);
    var img = document.createElement('IMG');
    img.src = pathToImages + 'down_time.gif';
    hourDiv.appendChild(img);
    hourDiv.className = 'selectBoxTime';
    if (Opera) {
        hourDiv.style.width = '30px';
        img.style.cssText = 'float:right';
        img.style.position = 'relative';
        img.style.styleFloat = 'right';
    }
    var hourPicker = createHourDiv();
    hourPicker.style.left = '170px';
    hourPicker.style.width = '35px';
    hourPicker.id = 'hourDropDown';
    calendarDiv.appendChild(hourPicker);
    var minuteDiv = document.createElement('DIV');
    minuteDiv.onmouseover = highlightSelect;
    minuteDiv.onmouseout = highlightSelect;
    minuteDiv.onclick = showMinuteDropDown;
    minuteDiv.style.width = '30px';
    var span = document.createElement('SPAN');
    span.innerHTML = currentMinute;
    span.id = 'calendar_minute_txt';
    minuteDiv.appendChild(span);
    timeBar.appendChild(minuteDiv);
    var img = document.createElement('IMG');
    img.src = pathToImages + 'down_time.gif';
    minuteDiv.appendChild(img);
    minuteDiv.className = 'selectBoxTime';
    if (Opera) {
        minuteDiv.style.width = '30px';
        img.style.cssText = 'float:right';
        img.style.position = 'relative';
        img.style.styleFloat = 'right';
    }
    var minutePicker = createMinuteDiv();
    minutePicker.style.left = '204px';
    minutePicker.style.width = '35px';
    minutePicker.id = 'minuteDropDown';
    calendarDiv.appendChild(minutePicker);
    return timeBar;
}
function writeBottomBar()
{
    var d = new Date();
    var bottomBar = document.createElement('DIV');
    bottomBar.id = 'bottomBar';
    bottomBar.style.cursor = 'pointer';
    bottomBar.className = 'todaysDate';
    var subDiv = document.createElement('DIV');
    subDiv.onclick = pickTodaysDate;
    subDiv.id = 'todaysDateString';
    subDiv.style.width = (calendarDiv.offsetWidth - 95) + 'px';
    var day = d.getDay();
    if (!weekStartsOnSunday) {
        if (day == 0)
            day = 7;
        day--;
    }
    var bottomString = todayStringFormat;
    bottomString = bottomString.replace('[monthString]', monthArrayShort[d.getMonth()]);
    bottomString = bottomString.replace('[day]', d.getDate());
    bottomString = bottomString.replace('[year]', d.getFullYear());
    bottomString = bottomString.replace('[dayString]', dayArray[day].toLowerCase());
    bottomString = bottomString.replace('[UCFdayString]', dayArray[day]);
    bottomString = bottomString.replace('[todayString]', todayString);
    subDiv.innerHTML = todayString + ': ' + d.getDate() + '. ' + monthArrayShort[d.getMonth()] + ', ' + d.getFullYear();
    subDiv.innerHTML = bottomString;
    bottomBar.appendChild(subDiv);
    var timeDiv = writeTimeBar();
    bottomBar.appendChild(timeDiv);
    calendarDiv.appendChild(bottomBar);
}
function getTopPos(inputObj)
{
    var returnValue = inputObj.offsetTop + inputObj.offsetHeight;
    while ((inputObj = inputObj.offsetParent) != null)
        returnValue += inputObj.offsetTop;
    return returnValue + calendar_offsetTop;
}
function getleftPos(inputObj)
{
    var returnValue = inputObj.offsetLeft;
    while ((inputObj = inputObj.offsetParent) != null)
        returnValue += inputObj.offsetLeft;
    return returnValue + calendar_offsetLeft;
}
function positionCalendar(inputObj)
{
    calendarDiv.style.left = getleftPos(inputObj) + 'px';
//    calendarDiv.style.top = getTopPos( inputObj ) + 'px';
    calendarDiv.style.top = (self.pageYOffset + getTopPos(inputObj)) + 'px';
    if (iframeObj) {
        iframeObj.style.left = calendarDiv.style.left;
        iframeObj.style.top = calendarDiv.style.top;
        iframeObj2.style.left = calendarDiv.style.left;
        iframeObj2.style.top = calendarDiv.style.top;
    }
}
function initCalendar()
{
    if (MSIE) {
        iframeObj = document.createElement('IFRAME');
        iframeObj.style.filter = 'alpha(opacity=0)';
        iframeObj.style.position = 'absolute';
        iframeObj.border = '0px';
        iframeObj.style.border = '0px';
        iframeObj.style.backgroundColor = '#FF0000';
        iframeObj2 = document.createElement('IFRAME');
        iframeObj2.style.position = 'absolute';
        iframeObj2.border = '0px';
        iframeObj2.style.border = '0px';
        iframeObj2.style.height = '1px';
        iframeObj2.style.width = '1px';
        iframeObj2.src = 'blank.html';
        iframeObj.src = 'blank.html';
        document.body.appendChild(iframeObj2);
        document.body.appendChild(iframeObj);
    }
    calendarDiv = document.createElement('DIV');
    calendarDiv.id = 'calendarDiv';
    calendarDiv.style.zIndex = 1000;
    slideCalendarSelectBox();
    document.body.appendChild(calendarDiv);
    writeBottomBar();
    writeTopBar();
    if (!currentYear) {
        var d = new Date();
        currentMonth = d.getMonth();
        currentYear = d.getFullYear();
    }
    writeCalendarContent();
}
function setTimeProperties()
{
    if (!calendarDisplayTime) {
        document.getElementById('timeBar').style.display = 'none';
        document.getElementById('timeBar').style.visibility = 'hidden';
        document.getElementById('todaysDateString').style.width = '100%';
    } else {
        document.getElementById('timeBar').style.display = 'block';
        document.getElementById('timeBar').style.visibility = 'visible';
        document.getElementById('hourDropDown').style.top = document.getElementById('calendar_minute_txt').parentNode.offsetHeight + calendarContentDiv.offsetHeight + document.getElementById('topBar').offsetHeight + 'px';
        document.getElementById('minuteDropDown').style.top = document.getElementById('calendar_minute_txt').parentNode.offsetHeight + calendarContentDiv.offsetHeight + document.getElementById('topBar').offsetHeight + 'px';
        document.getElementById('minuteDropDown').style.right = '50px';
        document.getElementById('hourDropDown').style.right = '50px';
        document.getElementById('todaysDateString').style.width = '150px';
        document.getElementById('todaysDateString').style.textAlign = 'left';
    }
}
function calendarSortItems(a, b)
{
    return a / 1 - b / 1;
}
function displayCalendar(inputField, format, buttonObj, displayTime, timeInput)
{
    if (displayTime)
        calendarDisplayTime = true;
    else
        calendarDisplayTime = false;
    if (inputField.value.length > 6) {
        if (!inputField.value.match(/^[0-9]*?$/gi)) {
            var items = inputField.value.split(/[^0-9]/gi);
            var positionArray = new Object();
            positionArray.m = format.indexOf('mm');
            if (positionArray.m == -1)
                positionArray.m = format.indexOf('m');
            positionArray.d = format.indexOf('dd');
            if (positionArray.d == -1)
                positionArray.d = format.indexOf('d');
            positionArray.y = format.indexOf('yyyy');
            positionArray.h = format.indexOf('hh');
            positionArray.i = format.indexOf('ii');
            this.initialHour = '00';
            this.initialMinute = '00';
            var elements = ['y', 'm', 'd', 'h', 'i'];
            var properties = ['currentYear', 'currentMonth', 'inputDay', 'currentHour', 'currentMinute'];
            var propertyLength = [4, 2, 2, 2, 2];
            for (var i = 0; i < elements.length; i++) {
                if (positionArray[elements[i]] >= 0) {
                    window[properties[i]] = inputField.value.substr(positionArray[elements[i]], propertyLength[i]) / 1;
                }
            }
            currentMonth--;
        } else {
            var monthPos = format.indexOf('mm');
            currentMonth = inputField.value.substr(monthPos, 2) / 1 - 1;
            var yearPos = format.indexOf('yyyy');
            currentYear = inputField.value.substr(yearPos, 4);
            var dayPos = format.indexOf('dd');
            tmpDay = inputField.value.substr(dayPos, 2);
            var hourPos = format.indexOf('hh');
            if (hourPos >= 0) {
                tmpHour = inputField.value.substr(hourPos, 2);
                currentHour = tmpHour;
            } else {
                currentHour = '00';
            }
            var minutePos = format.indexOf('ii');
            if (minutePos >= 0) {
                tmpMinute = inputField.value.substr(minutePos, 2);
                currentMinute = tmpMinute;
            } else {
                currentMinute = '00';
            }
        }
    } else {
        var d = new Date();
        currentMonth = d.getMonth();
        currentYear = d.getFullYear();
        currentHour = '08';
        currentMinute = '00';
        inputDay = d.getDate() / 1;
    }
    inputYear = currentYear;
    inputMonth = currentMonth;
    if (!calendarDiv) {
        initCalendar();
    } else {
        if (calendarDiv.style.display == 'block') {
            closeCalendar();
            return false;
        }
        writeCalendarContent();
    }
    returnFormat = format;
    returnDateTo = inputField;
    positionCalendar(buttonObj);
    calendarDiv.style.visibility = 'visible';
    calendarDiv.style.display = 'block';
    if (iframeObj) {
        iframeObj.style.display = '';
        iframeObj.style.height = '140px';
        iframeObj.style.width = '195px';
        iframeObj2.style.display = '';
        iframeObj2.style.height = '140px';
        iframeObj2.style.width = '195px';
    }
    setTimeProperties();
    updateYearDiv();
    updateMonthDiv();
    updateMinuteDiv();
    updateHourDiv();
}
function displayCalendarSelectBox(yearInput, monthInput, dayInput, hourInput, minuteInput, buttonObj)
{
    if (!hourInput)
        calendarDisplayTime = false;
    else
        calendarDisplayTime = true;
    currentMonth = monthInput.options[monthInput.selectedIndex].value / 1 - 1;
    currentYear = yearInput.options[yearInput.selectedIndex].value;
    if (hourInput) {
        currentHour = hourInput.options[hourInput.selectedIndex].value;
        inputHour = currentHour / 1;
    }
    if (minuteInput) {
        currentMinute = minuteInput.options[minuteInput.selectedIndex].value;
        inputMinute = currentMinute / 1;
    }
    inputYear = yearInput.options[yearInput.selectedIndex].value;
    inputMonth = monthInput.options[monthInput.selectedIndex].value / 1 - 1;
    inputDay = dayInput.options[dayInput.selectedIndex].value / 1;
    if (!calendarDiv) {
        initCalendar();
    } else {
        writeCalendarContent();
    }
    returnDateToYear = yearInput;
    returnDateToMonth = monthInput;
    returnDateToDay = dayInput;
    returnDateToHour = hourInput;
    returnDateToMinute = minuteInput;
    returnFormat = false;
    returnDateTo = false;
    positionCalendar(buttonObj);
    calendarDiv.style.visibility = 'visible';
    calendarDiv.style.display = 'block';
    if (iframeObj) {
        iframeObj.style.display = '';
        iframeObj.style.height = calendarDiv.offsetHeight + 'px';
        iframeObj.style.width = calendarDiv.offsetWidth + 'px';
        iframeObj2.style.display = '';
        iframeObj2.style.height = calendarDiv.offsetHeight + 'px';
        iframeObj2.style.width = calendarDiv.offsetWidth + 'px'
    }
    setTimeProperties();
    updateYearDiv();
    updateMonthDiv();
    updateHourDiv();
    updateMinuteDiv();
}
function NumberFormat(number, decimals, dec_point, thousands_sep) {
    var n = number, prec = decimals;
    n = !isFinite(+n) ? 0 : +n;
    prec = !isFinite(+prec) ? 0 : Math.abs(prec);
    var sep = (typeof thousands_sep == "undefined") ? ',' : thousands_sep;
    var dec = (typeof dec_point == "undefined") ? '.' : dec_point;

    var s = (prec > 0) ? n.toFixed(prec) : Math.round(n).toFixed(prec); //fix for IE parseFloat(0.55).toFixed(0) = 0;

    var abs = Math.abs(n).toFixed(prec);
    var _, i;

    if (abs >= 1000) {
        _ = abs.split(/\D/);
        i = _[0].length % 3 || 3;

        _[0] = s.slice(0, i + (n < 0)) +
                _[0].slice(i).replace(/(\d{3})/g, sep + '$1');

        s = _.join(dec);
    } else {
        s = s.replace('.', dec);
    }

    return s;
}

function AlteraValidaCelular(campoCheckbox, form, nomeValida, campoValida, obriga) {
    var frm = document.forms[form];
    var numElementos = frm.elements.length;
    for (var i = 0; i < numElementos; i++) {
        if (frm.elements[i].name == campoCheckbox) {
            var campo = frm.elements[i].checked;
            if (campo) {
                GetId(nomeValida).value = '';
                GetId(nomeValida).setAttribute('valida', obriga + ',telefone_sp,' + campoValida);
                GetId(nomeValida).focus();
            } else {
                GetId(nomeValida).value = '';
                GetId(nomeValida).setAttribute('valida', obriga + ',telefone,' + campoValida);
                GetId(nomeValida).focus();
            }
        }
    }
}

function in_array(string, array)
{
    for (i = 0; i < array.length; i++)
    {
        if (array[i] == string)
        {
            return true;
        }
    }
    return false;
}

function AtualizaCampo(campo, span, campo_a_atualizar, tabela, id, camada) {
    if (!camada || camada === '0') {
        camada = "";
    } else {
        camada = "_" + camada;
    }
    var Retorno = function() {
        var ajax = Ajax.request;
        if (ajax.readyState == 4) {
            GetId('aguardando_' + campo_a_atualizar + "_" + id + camada).innerHTML = "";
            if (ajax.status == 200) {
                var json = eval('(' + ajax.responseText + ')');
                switch (json.resultado) {
                    case 'sim':
                        GetId(span).innerHTML = json.novo_valor;
                        campo.style.display = 'none';
                        GetId(span).style.display = 'block';
                        break;
                    case 'sem_valor':
                        alert('Deve ser iinserido um valor válido.');
                        break;
                    default:
                        alert('Ocorreu um erro\nContate o administrador do sistema');
                        break;
                }
            }
        } else if (ajax.readyState != 4)
            GetId('aguardando_' + campo_a_atualizar + "_" + id + camada).innerHTML = '<img src="imagem/aguardando.gif" />';
    };
    var valores = "acao=AtualizaCampo&campo=" + campo_a_atualizar + "&tabela=" + tabela + "&id=" + id + "&valor=" + campo.value;
    Ajax.Solicitacao("acao.php", valores, Retorno);
    Ajax.Solicitar();
}

function GifFeitoComJquery(id, tempo) {
    tempo = parseInt(tempo);
    setInterval(function() {
        $("#" + id).fadeIn(tempo);
        $("#" + id).fadeOut(tempo);
    }, 400);
}

function Log(erroException) {
    console.error(erroException);
}
function MostraHint() {
    document.getElementById("label").style.visibility = "visible";
}
function OcultaHint() {
    document.getElementById("label").style.visibility = "hidden";
}
function focoForm(form) {
    var formulario = GetId(form);
    for (var i = 0; i < formulario.length; i++) {
        if ((formulario.elements[i].type === 'text' || formulario.elements[i].type === 'password' || formulario.elements[i].type === 'textarea') && formulario.elements[i].disabled === false && formulario.elements[i].value === "") {
            formulario.elements[i].focus();
            return true;
        }
    }
}
function MarcarTodos(botao, frm) {
    var form = GetId(frm);
    var numElementos = form.elements.length;
    for (var i = 0; i < numElementos; i++) {
        if (form.elements[i].type == "checkbox") {
            form.elements[i].checked = botao.checked;
        }
    }
}
function formataParaAmericano(data) {
    data = data.split('/');
    return data[2] + '-' + data[1] + '-' + data[0];
}
function AbrePesquisa( campo, divMostrar ) {
    var div;
    if( GetId('divPesquisa') ){
        div = 'divPesquisa';
    } else {   
        div = divMostrar;
    }
    if ( GetId( div ).style.display == "none" ) {
        $( "#"+div ).slideDown( "slow" );
        if ( GetId( campo ) ) {
            GetId( campo ).focus();
        }
    } else {
        $( "#"+div ).slideUp( "slow" );
    }
}

document.onkeydown = function(e) {
    try {
        // Internet Explorer
        var keychar;
        try {
            keychar = String.fromCharCode(event.keyCode);
            e = event;
        } catch(err) {
            keychar = String.fromCharCode(e.keyCode);
        }
        // Firefox, Opera, Chrome e outros
        if (e.altKey) {
            $('body *').filter(':button').each(function(){
                if(keychar == $(this).attr('accesskey')) {
                    $(this).click();
                    return false;
                }
            });
        }
    } catch(errr) {
        console.log(errr);
    }
};

function cadastraPessoa(tipo) {
    var form = GetId("frm" + tipo);
    if(!Valida.ValidaForm(form.id)){
        return false;
    }
    // Padrao.Executa({
    //     form: form.id,
    //     botao: GetId("enviadados"+tipo),
    //     loadPagina: "condominio/listar_pessoas.php",
    //     loadDiv: 'conteudo',
    //     divAguardando: "aguardando_acao",
    //     parametros: PegaDados.Formulario(form.id) + "&tipo=" + tipo + "&acao=cadastro_pessoas",
    //     pagina: 'condominio/acao.php'
    // });
}