  // Toggle para exibir o campo de data de início da promoção
  document.getElementById('iniciar_promocao').addEventListener('change', function() {
    var dataInicioContainer = document.getElementById('data_inicio_container');
    if (this.checked) {
        dataInicioContainer.style.display = 'block';
    } else {
        dataInicioContainer.style.display = 'none';
    }
});