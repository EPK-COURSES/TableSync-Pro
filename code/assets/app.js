// app.js

window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.alert.ok[data-autohide="1"]').forEach(el => {
    setTimeout(() => { el.style.display = 'none'; }, 4000);
  });


  document.querySelectorAll('[data-href]').forEach(row => {
    row.addEventListener('click', (e) => {
      
      if (e.target.closest('a,button,select,input,textarea')) return;
      const href = row.getAttribute('data-href');
      if (href) window.location.href = href;
    });
  });
});
