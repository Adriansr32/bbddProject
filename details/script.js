function openLoanModal() {
    document.getElementById('loanModal').style.display = 'block';
  }
  function closeLoanModal() { 
    document.getElementById('loanModal').style.display = 'none';
    document.getElementById('loanResult').innerHTML = '';
  }
  function showNotification(message, type) {
    var container = document.getElementById('notification-container');
    var icon = type === 'success' ? '<i class=\'fa fa-check\' style=\'color:#28a745;margin-right:7px;\'></i>' : (type === 'error' ? '<i class=\'fa fa-times-circle\' style=\'color:#dc3545;margin-right:7px;\'></i>' : '');
    container.innerHTML = icon + message;
    container.style.display = 'block';
    container.style.background = type === 'success' ? '#d4edda' : '#f8d7da';
    container.style.color = type === 'success' ? '#155724' : '#721c24';
    container.style.border = type === 'success' ? '1.5px solid #28a745' : '1.5px solid #dc3545';
    container.style.opacity = '0';
    container.style.animation = 'fadeInScale 0.5s forwards';
    setTimeout(function(){ container.style.display = 'none'; }, 3500);
  }
  if (typeof notification !== 'undefined' && notification) {
    showNotification(notification, notificationType);
  }