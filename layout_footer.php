</div><!-- /.page-content -->
</div><!-- /.main-wrapper -->

<script>
// Auto-dismiss alerts after 4 seconds
document.querySelectorAll('.alert').forEach(function(el) {
  setTimeout(function() {
    el.style.transition = 'opacity 0.5s';
    el.style.opacity = '0';
    setTimeout(function() { el.remove(); }, 500);
  }, 4000);
});

// Delete confirmation modal
function confirmDelete(id, name) {
  document.getElementById('deleteModal').classList.add('open');
  document.getElementById('deleteForm').action = 'delete.php?id=' + id;
  document.getElementById('deleteName').textContent = name;
}
function closeModal() {
  document.getElementById('deleteModal').classList.remove('open');
}
// Close modal on overlay click
document.addEventListener('click', function(e) {
  if (e.target.id === 'deleteModal') closeModal();
});
</script>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <h3>🗑️ Confirm Delete</h3>
    <p>Are you sure you want to delete <strong id="deleteName"></strong>? This action cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
      <form id="deleteForm" method="POST" style="display:inline;">
        <input type="hidden" name="_method" value="DELETE">
        <button type="submit" class="btn btn-primary">Yes, Delete</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>