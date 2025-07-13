// Clic sur ligne → affichage détaillé
document.querySelectorAll("#msg-table tbody tr[data-id]").forEach((row) => {
  row.addEventListener("click", function (e) {
    // si on a cliqué sur un bouton, ne pas naviguer
    if (e.target.closest("button")) {
      return;
    }
    const id = this.dataset.id;
    window.location.href = `profile.php?page=messages&view=${id}`;
  });
});
