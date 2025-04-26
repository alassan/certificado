document.addEventListener('DOMContentLoaded', function() {
    // Validação de datas
    $('#data_inicio, #data_fim').on('change', function() {
        const inicio = new Date($('#data_inicio').val());
        const fim = new Date($('#data_fim').val());
        
        if (inicio && fim && inicio > fim) {
            alert('Data de início não pode ser posterior à data de término!');
            $('#data_fim').val('');
        }
    });
    
    // Confirmação de exclusão
    $('.excluir-turma').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Tem certeza que deseja excluir esta turma?')) {
            window.location.href = `/turmas/excluir/${id}`;
        }
    });
    
    // DataTable
    $('#tabela-turmas').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
        },
        columnDefs: [
            { orderable: false, targets: [6] }
        ]
    });
    
    // Helper functions
    function getStatusTurma(dataInicio, dataFim) {
        const hoje = new Date();
        const inicio = new Date(dataInicio);
        const fim = new Date(dataFim);
        
        if (hoje < inicio) return 'Planejada';
        if (hoje >= inicio && hoje <= fim) return 'Em andamento';
        return 'Concluída';
    }
    
    function getStatusClass(status) {
        switch(status) {
            case 'Planejada': return 'info';
            case 'Em andamento': return 'success';
            case 'Concluída': return 'secondary';
            default: return 'light';
        }
    }
});