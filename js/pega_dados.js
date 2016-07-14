/**
 * Class responsavel em pegar dados do formularios
 * @autor Tiago Voltz
 * @atualizacao 03/07/2014
 * @hora 10:40
 * @vercao 2.0
 */
function PegaDados2() {
    
    this.Informacoes = "";
    
    this.valida = '';
    
    this.Formulario = function(form, valida) {
        this.valida = (valida === undefined || valida === '') ? true : false;
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
                case "fieldset":
                    break;
                default:
                    this.PegaDadosPADRAO(campo);
                    break;
            }
        }
        return this.Informacoes;
    };
    
    this.PegaDadosPADRAO = function(campo) {
        if (this.Informacoes.indexOf('&' + campo.name + '=') === -1) {
            if (campo.getAttribute("data-valida") === null) {
                if (this.valida) {
                    this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(campo.value) + "&" + campo.name + "_Valida=" + encodeURIComponent(",,," + campo.type);
                } else {
                    this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(campo.value);
                }
            } else {
                if (this.valida) {
                    this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(campo.value) + "&" + campo.name + "_Valida=" + encodeURIComponent(campo.getAttribute("data-valida") + "," + campo.type);
                } else {
                    this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(campo.value);
                }
            }
        }
    };
    
    this.PegaDadosCHECKBOX = function(campo) {
        if (campo.checked === true)
            this.PegaDadosPADRAO(campo);
    };
    
    this.PegaDadosRADIO = function(campo) {
        var obj = this.GetName(campo.name);
        for (var i = 0; i < obj.length; i++) {
            if (obj[i].checked) {
                this.PegaDadosPADRAO(obj[i]);
            }
        }
    };
    
    this.PegaDadosSELECTMULTIPLE = function(campo) {
        var valores = '';
        for (var i = 0; i < campo.options.length; i++) {
            if (campo.options[i].selected) {
                if (valores === '') {
                    valores = campo.options[i].value;
                } else {
                    valores += ';' + campo.options[i].value;
                }
            }
        }
        if (campo.getAttribute("data-valida") === null) {
            if (this.valida) {
                this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(valores) + "&" + campo.name + "_Valida=" + encodeURIComponent(",,," + campo.type);
            } else {
                this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(valores);
            }
        } else {
            if (this.valida) {
                this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(valores) + "&" + campo.name + "_Valida=" + encodeURIComponent(campo.getAttribute("valida") + "," + campo.type);
            } else {
                this.Informacoes += "&" + campo.name + "=" + encodeURIComponent(valores);
            }
        }
    };
    
    this.GetName = function(elemento) {
        return document.getElementsByName(elemento);
    };
}

var objPegaDados = new PegaDados2();