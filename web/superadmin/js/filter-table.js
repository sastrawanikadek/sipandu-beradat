const removeHTMLTags = (str) => {
  return str.replace(/<[^>]*>/g, "");
};

const setupFilterDataTable = (id, unorderableCols, tableData) => {
  $(`#${id} thead tr`).clone(true).appendTo(`#${id} thead`);
  $(`#${id} thead tr:eq(1) th`).each(function (i) {
    const title = $(this).text();
    const type = $(this).data("type");

    if (!type) {
      $(this).html("");
      return;
    }

    if (type === "select") {
      const colData = [
        ...new Set(Array.from(tableData.map((v) => removeHTMLTags(v[i])))),
      ];
      $(this).html(
        `<select class='form-control'><option value=''>Pilih ${title}</option></select>`
      );
      colData.map((v) =>
        $("select", this).append(`<option value="${v}">${v}</option>`)
      );
      $("select", this).on("change", function () {
        if (table.column(i).search() !== this.value) {
          table.column(i).search(this.value).draw();
        }
      });
    } else {
      $(this).html(
        `<input type="${type}" class="form-control" placeholder="Filter ${title}" />`
      );
      $("input", this).on("keyup change", function () {
        if (table.column(i).search() !== this.value) {
          table.column(i).search(this.value).draw();
        }
      });
    }
  });

  const table = $(`#${id}`).DataTable({
    destroy: true,
    orderCellsTop: true,
    fixedHeader: true,
    columnDefs: [
      {
        orderable: false,
        targets: unorderableCols,
      },
    ],
    data: tableData,
  });
};
