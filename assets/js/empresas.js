document.addEventListener('DOMContentLoaded', function() {
    // Máscaras
    $('.cnpj').mask('00.000.000/0000-00');
    $('.telefone').mask('(00) 00000-0000');
    
    // Validação de CNPJ único
    $('#cnpj').on('blur', function() {
        const cnpj = $(this).val().replace(/\D/g, '');
        if (cnpj.length === 14) {
            $.ajax({
                url: '/empresas/verificar-cnpj',
                method: 'POST',
                data: { cnpj: cnpj },
                success: function(response) {
                    if (response.existe) {
                        alert('CNPJ já cadastrado no sistema!');
                        $('#cnpj').val('').focus();
                    }
                }
            });
        }
    });
    
    // Confirmação de exclusão
    $('.excluir-empresa').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Tem certeza que deseja excluir esta empresa?')) {
            window.location.href = `/empresas/excluir/${id}`;
        }
    });
    
    // DataTable
    $('#tabela-empresas').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
        },
        columnDefs: [
            { orderable: false, targets: [4] }
        ]
    });
});