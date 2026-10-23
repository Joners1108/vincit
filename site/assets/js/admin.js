document.querySelectorAll(".sidebar-toggle").forEach(function (btn) {
  btn.addEventListener("click", function () {
    document.querySelector(".admin-sidebar")?.classList.toggle("active");
  });
});

/* Pesquisa rápida nas tabelas (campo "Pesquisar na tabela…") */
document.querySelectorAll(".table-card .table-search").forEach(function (input) {
  input.addEventListener("input", function () {
    var needle = this.value.toLowerCase().trim();
    var card = this.closest(".table-card");
    if (!card) return;
    var tbody = card.querySelector(".admin-table tbody");
    if (!tbody) return;
    tbody.querySelectorAll("tr").forEach(function (row) {
      var text = row.textContent.toLowerCase();
      row.style.display = needle === "" || text.indexOf(needle) !== -1 ? "" : "none";
    });
  });
});
