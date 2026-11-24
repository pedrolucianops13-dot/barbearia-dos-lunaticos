'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const pegar = (id) => document.getElementById(id);

    // 1. Máscara automática de Telefone / WhatsApp 
    const inputsTelefone = document.querySelectorAll('input[type="tel"], input[name="telefone"]');
    inputsTelefone.forEach((campo) => {
        campo.addEventListener('input', () => {
            let n = campo.value.replace(/\D/g, '').slice(0, 11);
            if (n.length <= 2) {
                campo.value = n;
            } else {
                const ddd = n.slice(0, 2);
                const parte1 = n.slice(2, n.length > 10 ? 7 : 6);
                const parte2 = n.length > 6 ? '-' + n.slice(n.length > 10 ? 7 : 6) : '';
                campo.value = `(${ddd}) ${parte1}${parte2}`;
            }
        });
    });

    // 2. Validação personalizada em tempo real (Nome e E-mail)
    const validarCampo = (campo) => {
        const valor = campo.value.trim();
        let erro = '';

        if (campo.hasAttribute('required') && !valor) {
            erro = 'Este campo é obrigatório.';
        } else if (campo.name === 'nome' && valor.length > 0 && valor.length < 3) {
            erro = 'Informe um nome válido com pelo menos 3 caracteres.';
        } else if (campo.type === 'email' && valor.length > 0 && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
            erro = 'Informe um e-mail válido.';
        } else if ((campo.name === 'telefone' || campo.type === 'tel') && valor.length > 0) {
            const digitos = valor.replace(/\D/g, '');
            if (!/^[1-9]{2}\d{8,9}$/.test(digitos)) {
                erro = 'Informe 10 ou 11 dígitos com DDD.';
            }
        }

        campo.setCustomValidity(erro);
        campo.setAttribute('aria-invalid', String(!!erro));
        return !erro;
    };

    const camposFormulario = document.querySelectorAll('form input, form textarea, form select');
    camposFormulario.forEach((campo) => {
        campo.addEventListener('blur', () => validarCampo(campo));
        campo.addEventListener('input', () => {
            if (campo.getAttribute('aria-invalid') === 'true') {
                validarCampo(campo);
            }
        });
    });

    // 3. Intercepta o envio APENAS se houver erro; se estiver tudo certo, deixa o PHP processar
    const formularios = document.querySelectorAll('form');
    formularios.forEach((form) => {
        form.addEventListener('submit', (evento) => {
            const inputs = Array.from(form.querySelectorAll('input, textarea, select'));
            const invalidos = inputs.filter((el) => !validarCampo(el));

            if (invalidos.length > 0) {
                evento.preventDefault();
                invalidos[0].focus();
                invalidos[0].reportValidity();
            }
        });
    });

    // 4. Confirmação visual antes de cancelar agendamento no Painel Administrativo
    const botoesCancelar = document.querySelectorAll('.painel-cancelar');
    botoesCancelar.forEach((botao) => {
        botao.addEventListener('click', (evento) => {
            const confirmar = window.confirm('Tem certeza que deseja cancelar este agendamento? O horário voltará a ficar disponível.');
            if (!confirmar) {
                evento.preventDefault();
            }
        });
    });
});
