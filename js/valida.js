
var Valida = {



    Erro: [],
    senha: "",
    email: "",
    CampoFocus: "",
    CampoFocusLoad: "",
    erCEP: RegExp ( /^\d{5}\-\d{3}$/ ),
    erCPF: RegExp ( /^\d{3}\.\d{3}\.\d{3}\-\d{2}$/ ),
    erCNPJ: RegExp ( /^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$/ ),
    erHORA: RegExp ( /^([0-1][0-9]|[2][0-3]):[0-5][0-9]$/ ),
    erQTDHORA: RegExp ( /^(\d{4}):[0-5][0-9]$/ ),
    erNUMERO: RegExp ( /^\d{0,}$/ ),
    erNUMEROPOSITIVO: RegExp ( /^[1-9]|[1-9][0-9]+$/ ),
    erNUMEROPONTO: RegExp ( /^[-]?[0-9]+([\.][0-9]+)?$/ ),
    erMOEDA: RegExp ( /^\d{1,3}(\.\d{3})*\,\d{2}$/ ),
    erTELEFONE: RegExp ( /^\(?\d{2}\)?\d{4}-\d{4}$/ ),
    erTELEFONE_SP: RegExp ( /^\(?\d{2}\)?\d{5}-\d{4}$/ ),
    erFAX: RegExp ( /^\(?\d{2}\)?\d{4}-\d{4}$/ ),
    erCELULAR: RegExp ( /^\(?\d{2}\)?\d{4}-\d{4}$/ ),
    erPORCENTAGEM: RegExp ( /^[0-9]$/ ),
    erEMAIL: RegExp ( /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-zA-Z]{2,6}(?:\.[a-zA-Z]{2})?)$/ ),
    erDATA: RegExp ( /^((0[1-9]|[12]\d)\/(0[1-9]|1[0-2])|30\/(0[13-9]|1[0-2])|31\/(0[13578]|1[02]))\/(19|20)?\d{4}$/ ),
    erDATAMESDIA: RegExp ( /^([0-9]{2}\/[0-9]{2}\/[0-9]{2})$/ ),
    erURL: RegExp ( /^(http[s]?:\/\/|ftp:\/\/)?(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i ),
    erMASCARAS: RegExp ( /^(cpf_cnpj|cpf|cnpj|telefone|telefone_sp|telefone_optativo|fax|celular|cep|data|hora|moeda|porcentagem|numero|numeropositivo|qtdhora)$/ ),
    erNAOVALIDAR: RegExp ( /^(submit|button|reset)$/ ),
    erLINGUA_FORM: RegExp ( /^(br|es|en)$/i ),
    erELEMENTOS_FORM: RegExp ( /^(fieldset|button)$/i ),
    MaskCNPJ: String ( '99.999.999/9999-99' ),
    MaskCPF: String ( '999.999.999-99' ),
    MaskNUMERO: String ( '99999999999999' ),
    MaskNUMEROPOSITIVO: String ( '99999999999999' ),
    MaskTELEFONE: String ( '(99)9999-9999' ),
    MaskTELEFONE_SP: String ( '(99)99999-9999' ),
    MaskFAX: String ( '(99)9999-9999' ),
    MaskCELULAR: String ( '(99)9999-9999' ),
    MaskDATA: String ( '99/99/9999' ),
    MaskDATAMESDIA: String ( '99/99' ),
    MaskCEP: String ( '99999-999' ),
    MaskHORA: String ( '99:99' ),
    MaskQTDHORA: String ( '9999:99' ),
    MaskMOEDA: String ( 'moeda' ),
    MaskPORCENTAGEM: String ( 'porcentagem' ),
    MsgBR: String ( 'Favor Preencher Corretamente: ' ),
    MsgEN: String ( 'Please Complete The Field: ' ),
    MsgES: String ( 'Por Favor, Rellene el Campo: ' ),
    ValidaForm: function( form ) {
        this.Erro = [];
        this.CampoFocus = "";
        var frm = document.forms[form];
        for ( var i = 0; i < frm.elements.length; i++ ) {
            var campo = frm.elements[i];
            if ( !this.erELEMENTOS_FORM.test ( campo.nodeName ) ) {
                if ( !this.erNAOVALIDAR.test ( campo.type ) ) {
                    eval ( "this.Verifica" + ( campo.type.replace ( /[^a-zA-Z]/g, "" ) ).toUpperCase () + "(campo)" );
                }
            }
        }
        if ( this.Erro.length == 0 ) {
            return true;
        } else {
            var msg = eval ( "this.Msg" + this.LinguaForm ( form, false ).toUpperCase () );
            alert ( msg + " \n\t- " + this.Erro.join ( "\n\t- " ) );
            this.CampoFocus.focus ();
            return false;
        }
    },
    ValidaCampoUnico: function(campo){
        this.Erro = [];
        this.CampoFocus = "";
        if ( !this.erELEMENTOS_FORM.test ( campo.nodeName ) ) {
            if ( !this.erNAOVALIDAR.test ( campo.type ) ) {
                eval ( "this.Verifica" + ( campo.type.replace ( /[^a-zA-Z]/g, "" ) ).toUpperCase () + "(campo)" );
            }
        }
        if ( this.Erro.length == 0 ) {
            return true;
        } else {
            var msg = eval ( "this.MsgBR" );
            alert ( msg + " \n\t- " + this.Erro.join ( "\n\t- " ) );
            this.CampoFocus.focus ();
            return false;
        }
    },
    ValidaDiv: function( div ) {
        this.Erro = [];
        this.CampoFocus = "";
        var campos = $ ( "#" + div + " input, #" + div + " select, #" + div + " textarea" ).each ( function() {
            return GetId ( $ ( this ).attr ( 'name' ) );
        } );
        for ( var i = 0; i < campos.length; i++ )
        {
            var campo = campos[i];
            if ( !this.erELEMENTOS_FORM.test ( campo.nodeName ) )
            {
                if ( !this.erNAOVALIDAR.test ( campo.type ) ){
                    eval ( "this.Verifica" + ( campo.type.replace ( /[^a-zA-Z]/g, "" ) ).toUpperCase () + "(campo)" );
                }
            }
        }
        if ( this.Erro.length == 0 )
        {
            return true;
        }
        else
        {
            var lang = ( GetId ( div ).lang ) ? GetId ( div ).lang : 'br';
            var msg = eval ( "this.Msg" + this.LinguaForm ( lang, true ).toUpperCase () );
            alert ( msg + " \n\t- " + this.Erro.join ( "\n\t- " ) );
            this.CampoFocus.focus ();
            return false;
        }
    },
    VerificaHIDDEN: function( campo ) {
        campo.value = this.Trim ( campo.value );
    },
    VerificaAPPLICATIONPDF: function( campo ) {
    },
    VerificaTEXT: function( campo ) {
        //campo.value = this.Trim ( campo.value );
        if ( ( this.ValorValida ( campo, 0 ) == "sim" ) || ( campo.value != "" ) ) {
            var Verificador = this.ValorValida ( campo, 1 );
            ( Verificador == '' ) ? this.VerificaPADRAO ( campo ) : eval ( "this.Verifica" + Verificador.toUpperCase () + "(campo)" );
        }
    },
    VerificaTEL: function( campo ) {
        campo.value = this.Trim ( campo.value );
        if ( ( this.ValorValida ( campo, 0 ) == "sim" ) || ( campo.value != "" ) ) {
            var Verificador = this.ValorValida ( campo, 1 );
            ( Verificador == '' ) ? this.VerificaPADRAO ( campo ) : eval ( "this.Verifica" + Verificador.toUpperCase () + "(campo)" );
        }
    },
    VerificaTEXTAREA: function( campo ) {
        campo.value = this.Trim ( campo.value, 'nao' );
        if ( ( this.ValorValida ( campo, 0 ) == "sim" ) && ( campo.value == "" ) )
            this.MarcaCampo ( campo );
    },
    VerificaPASSWORD: function( campo )
    {
        if ( ( this.ValorValida ( campo, 0 ) == "sim" ) || ( campo.value != "" ) )
        {
            var Verificador = this.ValorValida ( campo, 1 );
            ( Verificador != '' ) ? eval ( "this.Verifica" + Verificador.toUpperCase () + "(campo)" ) : this.VerificaSENHA ( campo );
        }
    },
    VerificaSELECTONE: function( campo )
    {
        if ( this.ValorValida ( campo, 0 ) == "sim" && ( campo.value == '' || campo.value == 0 || campo.value == 'selecione' ) )
        {
            this.MarcaCampo ( campo );
        }
    },
    VerificaSELECTMULTIPLE: function( campo )
    {
        var Verificador = this.ValorValida ( campo, 1 );
        if ( Verificador == 'todos' ) {
            for ( var i = 0; i < campo.options.length; i++ )
            {
                campo.options[i].selected = true;
            }
        }
        if ( this.ValorValida ( campo, 0 ) == "sim" )
        {
            for ( var i = 0; i < campo.options.length; i++ )
            {
                if ( campo.options[i].selected )
                {
                    return true;
                }
            }
            this.MarcaCampo ( campo );
        }
    },
    VerificaCHECKBOX: function( campo )
    {
        if ( ( this.ValorValida ( campo, 0 ) == "sim" ) && campo.checked == false)
        {
            if( campo.getAttribute("data-validatodos") )
            {
                if(campo.getAttribute("data-validatodos") == 'sim')
                    this.MarcaCampo ( campo );
                else
                {
                    //verifica o grupo que ele está
                    var grupo = campo.getAttribute('data-validagrupo');
                    var verificador = true;
                    
                    //verifica se tem algum marcado no grupo
                    $('input[data-validagrupo='+grupo+']').each(function(){
                        if(this.checked)
                            verificador = false;
                    });
                    
                    //caso não haja, aciona valida
                    if(verificador)
                        this.MarcaCampo ( campo );
                }
            }
            else
                this.MarcaCampo ( campo );
        }
    },
    VerificaRADIO: function( campo )
    {
        if ( this.ValorValida ( campo, 0 ) == "sim" )
        {
            var obj = this.GetName ( campo.name );
            for ( var i = 0; i < obj.length; i++ )
            {
                if ( obj[i].checked )
                    return true;
            }
            this.MarcaCampo ( campo );
        }
    },
    VerificaFILE: function( campo )
    {
        if ( ( this.ValorValida ( campo, 0 ) == "sim" ) && ( campo.value == "" ) )
            this.MarcaCampo ( campo );
    },
    ProcuraForm: function()
    {
        var i = 0;
        while ( document.forms[i] )
        {
            Valida.AddEventosForm ( document.forms[i] );
            i++;
        }
    },
    AddEventosForm: function( form )
    {
        this.CampoFocusLoad = "";
        var frm = form;
        for ( var i = 0; i < frm.elements.length; i++ )
        {
            var campo = frm.elements[i];
            if ( ( !( /hidden|button|checkbox|undefined/i.test ( campo.type ) ) ) && ( this.CampoFocusLoad == "" ) && ( campo.disabled == false ) )
            {
                campo.value = this.Trim ( campo.value );
                //campo.focus();
                this.CampoFocusLoad = campo;
            }
            if ( this.erMASCARAS.test ( this.ValorValida ( campo, 1 ) ) )
            {
                if ( !( /select-multiple/i.test ( campo.type ) ) ) {
                    this.AddEventosMascara ( campo );
                }
            }
            else if ( campo.getAttribute ( "valida" ) != null )
            {
                if ( !( /select-multiple/i.test ( campo.type ) ) ) {
                    eval ( "this.AddEvento(campo, 'blur', function(){Valida.Verifica" + ( campo.type.replace ( /[^a-zA-Z]/g, "" ) ).toUpperCase () + "(this);});" );
                }
            }
        }
    },
    AddEventosMascara: function( campo )
    {
        var eventos = new Array ( "keyup", "keypress", "change", "keydown", "blur", "paste" );
        for ( var j = 0; j < eventos.length; j++ )
        {
            this.AddEvento ( campo, eventos[j], function()
            {
                Valida.ColocaMascara ( this );
            } );
            eval ( "this.AddEvento(campo, '" + eventos[j] + "', function(){Valida.Verifica" + ( campo.type.replace ( /[^a-zA-Z]/g, "" ) ).toUpperCase () + "(this);});" );
        }
    },

    telefone_optativo: function (campo) {
        if(campo.value.length > 13) {
            return 'TELEFONE_SP';
        }  else {
            return 'TELEFONE';
        }
    },

    cpf_cnpj: function (campo) {
        if(campo.value.length > 14) {
            return 'CNPJ';
        }  else {
            return 'CPF';
        }
    },

    ColocaMascara: function( campo )
    {
        var funcao = this.ValorValida ( campo, 1 );

        if(funcao == 'telefone_optativo' || funcao == 'telefone' || funcao == 'telefone_sp' ){
            funcao = this.telefone_optativo(campo);
        }
        if(funcao == 'cpf_cnpj'){
            funcao = this.cpf_cnpj(campo);
        }

        var mascara = eval ( "this.Mask" + funcao.toUpperCase () );

        if ( mascara == undefined ) {
            return false;
        }
        if ( mascara == 'moeda' ) {
            campo.value = this.MascaraMOEDA ( campo.value );
            return true;
        } else if ( mascara == 'porcentagem' ) {
            campo.value = this.MascaraPORCENTAGEM ( campo.value );
            return true;
        }
        var i = 0;
        var cont = 0;
        var valor = "";

        //verificar se sÃ£o apenas nÃºmeros positivos
        if ( this.ValorValida ( campo, 1 ).toUpperCase () == 'NUMEROPOSITIVO' )
            ValorCampo = this.SomenteNumeroPositivo ( campo.value );
        else
            ValorCampo = this.SomenteNumero ( campo.value );

        var mskLen = ValorCampo.length;
        while ( i <= mskLen )
        {
            if ( ValorCampo.charAt ( cont ) )
            {
                if ( mascara.charAt ( i ) == 9 )
                {
                    valor += ValorCampo.charAt ( cont );
                    cont++;
                }
                else
                {
                    valor += mascara.charAt ( i );
                    mskLen++;
                }
            }
            if ( valor.length == mascara.length )
                break;
            i++;
        }
        campo.value = valor;
    },
    MascaraPORCENTAGEM: function( valor )
    {
        var SeparadorDecimal = ",";
        var SeparadorMilesimo = ".";
        var strCheck = '0123456789';
        var len = valor.length;
        for ( var i = 0; i < len; i++ )
            if ( ( valor.charAt ( i ) != '0' ) && ( valor.charAt ( i ) != SeparadorDecimal ) )
                break;
        var aux = '';
        for ( ; i < len; i++ )
            if ( strCheck.indexOf ( valor.charAt ( i ) ) != -1 )
                aux += valor.charAt ( i );
        len = aux.length;
        if ( len == 0 )
            valor = '';
        if ( len == 1 )
            valor = '0' + SeparadorDecimal + '0' + aux;
        if ( len == 2 )
            valor = '0' + SeparadorDecimal + aux;
        if ( len > 2 )
        {
            var aux2 = '';
            for ( var j = 0, i = len - 3; i >= 0; i-- ) {
                if ( j == 3 )
                {
                    aux2 += SeparadorMilesimo;
                    j = 0;
                }
                aux2 += aux.charAt ( i );
                j++;
            }
            valor = '';
            var len2 = aux2.length;
            for ( i = len2 - 1; i >= 0; i-- )
                valor += aux2.charAt ( i );
            valor += SeparadorDecimal + aux.substr ( len - 2, len );
        }
        return valor;
    },
    MascaraMOEDA: function( valor ) {
        var SeparadorDecimal = ",";
        var SeparadorMilesimo = ".";
        var strCheck = '0123456789';
        var len = valor.length;
        for ( var i = 0; i < len; i++ )
            if ( ( valor.charAt ( i ) != '0' ) && ( valor.charAt ( i ) != SeparadorDecimal ) )
                break;
        var aux = '';
        for ( ; i < len; i++ )
            if ( strCheck.indexOf ( valor.charAt ( i ) ) != -1 )
                aux += valor.charAt ( i );
        len = aux.length;
        if ( len == 0 )
            valor = '';
        if ( len == 1 )
            valor = '0' + SeparadorDecimal + '0' + aux;
        if ( len == 2 )
            valor = '0' + SeparadorDecimal + aux;
        if ( len > 2 ) {
            var aux2 = '';
            for ( var j = 0, i = len - 3; i >= 0; i-- ) {
                if ( j == 3 ) {
                    aux2 += SeparadorMilesimo;
                    j = 0;
                }
                aux2 += aux.charAt ( i );
                j++;
            }
            valor = '';
            var len2 = aux2.length;
            for ( i = len2 - 1; i >= 0; i-- )
                valor += aux2.charAt ( i );
            valor += SeparadorDecimal + aux.substr ( len - 2, len );
        }
        return valor;
    },
    SomenteNumero: function( valor ) {
        return valor.replace ( /[^0-9]/g, "" );
    },
    SomenteNumeroPositivo: function( valor ) {
        return valor.replace ( /^0|[a-zA-Z]/g, "" );
    },
    SomenteLetras: function( valor ) {
        return valor.replace ( /[^a-zA-Z]/g, "" );
    },
    LinguaForm: function( form, div ) {
        var lingua = "";
        if ( div !== false && div !== undefined &&  div != 'undefined' ) {
            return form;
        }
        if ( document.forms[form].getAttribute ( "lingua_form" ) != null ) {
            lingua = document.forms[form].getAttribute ( "lingua_form" );
            if ( !( this.erLINGUA_FORM.test ( lingua ) ) ){
                lingua = document.forms[form].getAttribute ( "lingua_form" ).value;
            }
        } else {
            lingua = "br";
        }
        return lingua;
    },
    Maiusculo: function( form ) {
        var frm = document.forms[form];
        for ( var i = 0; i < frm.elements.length; i++ ) {
            var campo = frm.elements[i];
            if ( /text|textarea/i.test ( campo.type ) ) {
                if ( ( !( /email|url/i.test ( this.ValorValida ( campo, 1 ) ) ) ) && ( campo.value != "" ) )
                    campo.value = campo.value.toUpperCase ();
            }
        }
    },
    PrimeiraLetraMaiusculo: function( form ) {
        var frm = document.forms[form];
        for ( var i = 0; i < frm.elements.length; i++ ) {
            var campo = frm.elements[i];
            if ( /text|textarea/i.test ( campo.type ) ) {
                if ( ( !( /email|url/i.test ( this.ValorValida ( campo, 1 ) ) ) ) && ( campo.value != "" ) ) {
                    campo.value = campo.value.replace ( /(^[^0-9])|(\s[^0-9])/g,
                            function( str, p1, p2, offset, s ) {
                                return str.toUpperCase ();
                            } );
                }
            }
        }
    },
    Trim: function( text, espaco ) {
        if ( text == undefined )
            return false;
        try
        {
            var texto = text.replace ( /^\s+|\s+$/g, "" );
            if ( espaco != "nao" ) {
                texto = texto.replace ( /\s{2,}/g, ' ' );
            }
        } catch ( e )
        {
        }
        return texto;
    },
    AddErro: function( campo ) {
        var Msg = this.ValorValida ( campo, 2 );
        for ( var i = 0; i < this.Erro.length; i++ ) {
            if ( this.Erro[i] == Msg )
                return true;
        }
        if ( this.CampoFocus == "" )
            this.CampoFocus = campo;
        this.Erro[this.Erro.length] = Msg;
    },
    MarcaCampo: function( campo ) {
        this.AddErro ( campo );
        if ( campo.type != "radio" && campo.type != "checkbox") {
            if ( campo.className != 'campoIII' )
                campo.className = ( campo.className != "" ) ? campo.className.replace ( "campo", "preencher" ) : "preencher";
            else
                campo.className = ( campo.className != "" ) ? campo.className.replace ( "campoIII", "preencherIII" ) : "preencherIII";
            campo.onfocus = new Function ( 'Valida.DesmarcaCampo(this);' );
        } else {
            var obj = this.GetName ( campo.name );
            for ( var i = 0; i < obj.length; i++ ) {
                //obj[i].className = (obj[i].className != "") ? obj[i].className.replace("campo", "preencher") : "preencher";
                //obj[i].onfocus = new Function('Valida.DesmarcaCampo(this);');
            }
        }
    },
    DesmarcaCampo: function( campo ) {
        if ( campo.type != "radio" ) {
            campo.className = ( campo.className != "" ) ? campo.className.replace ( "preencher", "campo" ) : "campo";
            campo.onfocus = null;
        } else {
            var obj = this.GetName ( campo.name );
            for ( var i = 0; i < obj.length; i++ ) {
                obj[i].className = ( obj[i].className != "" ) ? obj[i].className.replace ( "preencher", "campo" ) : "campo";
                obj[i].onfocus = null;
            }
        }
    },
    VerificaCPF: function( campo ) {
        var num_cpf = this.Trim ( campo.value );
        if ( !( this.erCPF.test ( num_cpf ) ) ) {
            this.MarcaCampo ( campo );
            return false;
        }
        var numCPF = this.SomenteNumero ( campo.value );
        if ( numCPF.length > 11 || numCPF == "00000000000" || numCPF == "11111111111" || numCPF == "22222222222" || numCPF == "33333333333" || numCPF == "44444444444" || numCPF == "55555555555" || numCPF == "66666666666" || numCPF == "77777777777" || numCPF == "88888888888" || numCPF == "99999999999" || numCPF == "12345678909" ) {
            this.MarcaCampo ( campo );
            return false;
        }
        var soma = 0;
        for ( var numi = 0; numi < 9; numi++ ) {
            soma += parseInt ( numCPF.charAt ( numi ) ) * ( 10 - numi );
            var resto = 11 - ( soma % 11 );
        }
        if ( resto == 10 || resto == 11 )
            resto = 0;
        if ( resto != parseInt ( numCPF.charAt ( 9 ) ) ) {
            this.MarcaCampo ( campo );
            return false;
        }
        soma = 0;
        for ( numi = 0; numi < 10; numi++ ) {
            soma += parseInt ( numCPF.charAt ( numi ) ) * ( 11 - numi );
            resto = 11 - ( soma % 11 );
        }
        if ( resto == 10 || resto == 11 )
            resto = 0;
        if ( resto != parseInt ( numCPF.charAt ( 10 ) ) ) {
            this.MarcaCampo ( campo );
            return false;
        }
        this.DesmarcaCampo ( campo );
        return true;
    },
    VerificaCNPJ: function( campo ) {
        var num_cnpj = this.Trim ( campo.value );
        if ( !( this.erCNPJ.test ( num_cnpj ) ) ) {
            this.MarcaCampo ( campo );
            return false;
        }
        var numCNPJ = this.SomenteNumero ( campo.value );
        var c = numCNPJ.substr ( 0, 12 );
        var dv = numCNPJ.substr ( 12, 2 );
        var d1 = 0;
        for ( var numi = 0; numi < 12; numi++ ) {
            d1 += c.charAt ( 11 - numi ) * ( 2 + ( numi % 8 ) );
        }
        if ( d1 == 0 ) {
            this.MarcaCampo ( campo );
            return false;
        }
        d1 = 11 - ( d1 % 11 );
        if ( d1 > 9 )
            d1 = 0;
        if ( dv.charAt ( 0 ) != d1 ) {
            this.MarcaCampo ( campo );
            return false;
        }
        d1 *= 2;
        for ( numi = 0; numi < 12; numi++ ) {
            d1 += c.charAt ( 11 - numi ) * ( 2 + ( ( numi + 1 ) % 8 ) );
        }
        d1 = 11 - ( d1 % 11 );
        if ( d1 > 9 )
            d1 = 0;
        if ( dv.charAt ( 1 ) != d1 ) {
            this.MarcaCampo ( campo );
            return false;
        }
        this.DesmarcaCampo ( campo );
        return true;
    },
    VerificaPORCENTAGEM: function( campo ) {
        ( !( this.erPORCENTAGEM.test ( campo.value ) ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
    },
    VerificaPADRAO: function( campo ) {
        ( campo.value == "" ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
    },
    VerificaSENHA: function( campo ) {
        ( campo.value.length < 6 ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
        this.senha = campo.value;
    },
    VerificaCONFIRMASENHA: function( campo ) {
        if ( campo.value.length >= 6 ) {
            if ( this.senha === campo.value ) {
                this.DesmarcaCampo ( campo );
            } else {
                this.MarcaCampo ( campo );
            }
        } else {
            this.MarcaCampo ( campo );
        }
    },
    VerificaMOEDA: function( campo ) {
        ( !( this.erMOEDA.test ( campo.value ) ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
    },
    VerificaURL: function( campo ) {
        ( !( this.erURL.test ( campo.value ) ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
    },

    VerificaTELEFONE: function( campo ) {
        this.VerificaTELEFONE_OPTATIVO(campo);
    },

    VerificaTELEFONE2: function( campo ) {
        ( !( this.erTELEFONE.test ( campo.value ) ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
    },

    VerificaTELEFONE_SP: function( campo ) {
        /// ( !( this.erTELEFONE_SP.test ( campo.value ) ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
        this.VerificaTELEFONE_OPTATIVO(campo);
    },

    VerificaTELEFONE_SP2: function( campo ) {
        ( !( this.erTELEFONE_SP.test ( campo.value ) ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
    },

    VerificaTELEFONE_OPTATIVO: function(campo) {
        if(campo.value.length > 13){
            this.VerificaTELEFONE_SP2(campo);
        } else {
            this.VerificaTELEFONE2(campo);
        }
    },
    VerificaCPF_CNPJ: function(campo) {
        if(campo.value.length > 14){
            this.VerificaCNPJ(campo);
        } else {
            this.VerificaCPF(campo);
        }
    },
    VerificaFAX: function( campo ) {
        ( !( this.erFAX.test ( campo.value ) ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
    },
    VerificaCELULAR: function( campo ) {
        ( !( this.erCELULAR.test ( campo.value ) ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
    },
    VerificaEMAIL: function( campo ) {
        campo.value = this.Trim ( campo.value );
        if ( ( this.ValorValida ( campo, 0 ) == "sim" ) || ( campo.value != "" ) ) {
            var valor = campo.value.split ( ", " );
            for ( var i = 0; i < valor.length; i++ ) {
                if ( !( this.erEMAIL.test ( valor[i] ) ) ) {
                    this.MarcaCampo ( campo );
                    return false;
                }
            }
        }
        this.email = campo.value;
    },
    VerificaCONFIRMAEMAIL: function( campo ) {
        if ( this.email === campo.value ) {
            this.DesmarcaCampo ( campo );
        } else {
            this.MarcaCampo ( campo );
        }
    },
    VerificaMascaraCampoIndividual: function( campo ) {
        var valida = campo.getAttribute('valida');
        var aux_mascara = valida.split(',');
        var mascara = aux_mascara[1].toUpperCase();
        if ( eval('this.er'+mascara+'.test ( campo.value )') ) {
            return true;
        } else {
            return false;
        }
    },
    VerificaNUMERO: function( campo ) {
        ( campo.value != "" ) ? ( !( this.erNUMERO.test ( campo.value ) ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo ) : this.MarcaCampo ( campo );
    },
    VerificaNUMEROPOSITIVO: function( campo ) {
        ( campo.value != "" ) ? ( !( this.erNUMEROPOSITIVO.test ( campo.value ) ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo ) : this.MarcaCampo ( campo );
    },
    VerificaNUMEROPONTO: function( campo ) {
        ( campo.value != "" ) ? ( isNaN ( campo.value ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo ) : this.MarcaCampo ( campo );
    },
    VerificaCEP: function( campo ) {
        if ( !( this.erCEP.test ( campo.value ) ) )
        {
            this.MarcaCampo ( campo )
            return false;
        }
        else
        {
            this.DesmarcaCampo ( campo );
            return true;
        }
    },
    VerificaDATA: function( campo ) {
        if ( this.erDATA.test ( campo.value ) ) {
            var dia = campo.value.substring ( 0, 2 );
            var mes = campo.value.substring ( 3, 5 );
            var ano = campo.value.substring ( 6, 10 );
            if ( ( ( mes == 4 ) || ( mes == 6 ) || ( mes == 9 ) || ( mes == 11 ) ) && dia > 30 ) {
                this.MarcaCampo ( campo );
                return false;
            } else if ( ( ( ano % 4 ) != 0 ) && ( mes == 2 ) && ( dia > 28 ) ) {
                this.MarcaCampo ( campo );
                return false;
            } else if ( ( ( ano % 4 ) == 0 ) && ( mes == 2 ) && ( dia > 29 ) ) {
                this.MarcaCampo ( campo );
                return false;
            }
        } else {
            this.MarcaCampo ( campo );
            return false;
        }
        this.DesmarcaCampo ( campo );
    },
    VerificaDATAMESDIA: function( campo ) {
        ( this.erDATAMESDIA.test ( campo.value ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
    },
    DiferencaDuasDatas: function( data1, data2 ) {
        var dia1 = data1.substring ( 0, 2 );
        var mes1 = data1.substring ( 3, 5 );
        var ano1 = data1.substring ( 6, 10 );
        var dia2 = data2.substring ( 0, 2 );
        var mes2 = data2.substring ( 3, 5 );
        var ano2 = data2.substring ( 6, 10 );
        ano1 = ano1 * 1;
        ano2 = ano2 * 1;
        if ( ano1 < 100 && ano1 < 20 ) {
            ano1 = ano1 + 2000;
        }
        if ( ano2 < 100 && ano2 > 20 ) {
            ano2 = ano2 + 1900;
        }
        if ( ano2 < 100 && ano2 < 20 ) {
            ano2 = ano2 + 2000;
        }
        firstdate = new Date ( mes1 + '/' + dia1 + '/' + ano1 );
        mes1 = firstdate.getMonth () + 1;
        dia1 = firstdate.getDate ();
        seconddate = new Date ( mes2 + '/' + dia2 + '/' + ano2 );
        mes2 = seconddate.getMonth () + 1;
        dia2 = seconddate.getDate ();
        var anos = ano2 - ano1;
        if ( mes2 == mes1 ) {
            if ( dia2 < dia1 ) {
                mes2 = mes2 + 12;
                anos = anos - 1;
            }
        }
        if ( mes2 < mes1 ) {
            mes2 = mes2 + 12;
            anos = anos - 1;
            meses = mes2 - mes1;
        }
        meses = mes2 - mes1;
        if ( dia2 < dia1 ) {
            meses = meses - 1;
            dia2 = dia2 + 30;
            if ( mes2 == mes1 ) {
                meses = 0;
                anos = anos - 1;
            }
        }
        var dias = dia2 - dia1;
        var retorno = new Array ( anos, meses, dias );
        return retorno;
    },
    AddNumeroDiasData: function( data, dias ) {
        var dia = data.substring ( 0, 2 );
        var mes = data.substring ( 3, 5 );
        var ano = data.substring ( 6, 10 );
        mes--;
        var dataAtual = new Date ( ano, mes, dia );
        dataAtual.setDate ( dataAtual.getDate () + dias );
        RetornoDia = ( dataAtual.getDate () < 10 ) ? '0' + dataAtual.getDate () : dataAtual.getDate ();
        RetornoMes = ( ( dataAtual.getMonth () + 1 ) < 10 ) ? '0' + ( dataAtual.getMonth () + 1 ) : ( dataAtual.getMonth () + 1 );
        RetornoAno = dataAtual.getFullYear ();
        return RetornoDia + '/' + RetornoMes + '/' + RetornoAno;
    },
    VerificaSeDataMaior: function( data1, data2 ) {
        var dia1 = data1.substring ( 0, 2 );
        var mes1 = data1.substring ( 3, 5 );
        var ano1 = data1.substring ( 6, 10 );
        var dia2 = data2.substring ( 0, 2 );
        var mes2 = data2.substring ( 3, 5 );
        var ano2 = data2.substring ( 6, 10 );
        var dataInicial = new Date ( ano1, mes1, dia1 );
        var dataFinal = new Date ( ano2, mes2, dia2 );
        if ( dataInicial < dataFinal ) {
            return 'menor';
        } else if ( dataInicial > dataFinal ) {
            return 'maior';
        } else {
            return 'igual';
        }
    },
    VerificaHORA: function( campo ) {
        ( !( this.erHORA.test ( campo.value ) ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
    },
    VerificaQTDHORA: function( campo ) {
        ( !( this.erQTDHORA.test ( campo.value ) ) ) ? this.MarcaCampo ( campo ) : this.DesmarcaCampo ( campo );
    },
    ValorValida: function( campo, pos ) {
        return ( campo.getAttribute ( "valida" ) != null ) ? campo.getAttribute ( "valida" ).split ( "," )[pos] : '';
    },
    ValorNumero: function( campo ) {
        if ( campo.getAttribute ( "numero" ) != null )
            return campo.getAttribute ( "numero" );
    },
    ValorAutocompleta: function( campo ) {
        return ( campo.getAttribute ( "autocompleta" ) != null ) ? campo.getAttribute ( "autocompleta" ) : '';
    },
    Get: function( elemento ) {
        return document.getElementById ( elemento );
    },
    GetName: function( elemento ) {
        return document.getElementsByName ( elemento );
    },
    AddEvento: function( campo, evento, funcao, tmp ) {
        tmp || ( tmp = true );
        if ( campo.attachEvent ) {
            campo["e" + evento + funcao] = funcao;
            campo[evento + funcao] = function() {
                campo["e" + evento + funcao] ( window.event );
            };
            campo.attachEvent ( "on" + evento, campo[evento + funcao] );
        } else {
            campo.addEventListener ( evento, funcao, true );
        }
    },
    RemoveEvento: function( campo, evento, funcao, tmp ) {
        tmp || ( tmp = true );
        try {
            if ( campo.detachEvent ) {
                campo.detachEvent ( "on" + evento, campo[evento + funcao] );
                campo[evento + funcao] = null;
            } else {
                campo.removeEventListener ( evento, funcao, true );
            }
        }
        catch ( err ) {
        }
    }
};
Valida.AddEvento ( window, "load", Valida.ProcuraForm );