const toggleBtn = document.getElementById('toggleSidebar');
const layout    = document.getElementById('layout');

toggleBtn.addEventListener('click', () => {
  layout.classList.toggle('sidebar-collapsed');
  toggleBtn.textContent = layout.classList.contains('sidebar-collapsed') ? '➡️' : '⬅️';
});
