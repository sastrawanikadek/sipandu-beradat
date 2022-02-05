$(document).ready(async () => {
  read_analytic();
});

$("#filter-bar").on("change", function () {
  if (this.value == 1) {
    read_analytic();
  } else if (this.value == 2) {
    read_analytic_month();
  } else if (this.value == 3) {
    read_analytic_year();
  }
});

const read_analytic = async () => {
  const idAkomodasi = localStorage.getItem("id_akomodasi");
  const req = await fetch(
    `https://sipanduberadat.com/api/admin-akomodasi/analytic/?id_akomodasi=${idAkomodasi}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    $("#total-pegawai").html(data.total_pegawai);
    $("#total-e-kulkul").html(data.total_sirine);
    $("#total-wisatawan").html(data.total_tamu);
    $("#total-daftar-hitam").html(data.total_block);
    $("#total-pelaporan").html(data.total_pelaporan);
    $("#total-pelaporan-hari-ini").html(data.total_today_pelaporan);
    $("#total-admin").html(data.total_admin);
    temp = data.this_week_pelaporan;
    data_pelaporan_mingguan = [
      temp.Minggu,
      temp.Senin,
      temp.Selasa,
      temp.Rabu,
      temp.Kamis,
      temp.Jumat,
      temp.Sabtu,
    ];
    temp = data.this_week_pelaporan_darurat;
    data_pelaporan_darurat_mingguan = [
      temp.Minggu,
      temp.Senin,
      temp.Selasa,
      temp.Rabu,
      temp.Kamis,
      temp.Jumat,
      temp.Sabtu,
    ];
    total_data_pelaporan = data_pelaporan_darurat_mingguan.map(function (
      num,
      idx
    ) {
      return num + data_pelaporan_mingguan[idx];
    });
    // data_pelaporan_mingguan = [3,4,5,6,7,8,9]
    // data_pelaporan_darurat_mingguan = [8,9,8,8,8,8,9]
    let total_pelaporan = 0;
    let total_pelaporan_darurat = 0;
    $.each(data_pelaporan_mingguan, function () {
      total_pelaporan += this || 0;
    });
    $.each(data_pelaporan_darurat_mingguan, function () {
      total_pelaporan_darurat += this || 0;
    });
    $("#total-pelaporan-darurat").html(total_pelaporan_darurat);
    $("#total-keluhan").html(total_pelaporan);

    var curr;
    curr = new Date();
    var sundayDate;
    sundayDate = new Date();
    var sunday;
    sunday = 0 - curr.getDay();
    sundayDate.setDate(sundayDate.getDate() + sunday);
    console.log(curr);
    console.log(sundayDate);
    var saturdayDate;
    saturdayDate = new Date();
    var saturday;
    saturday = 6 - curr.getDay();
    saturdayDate.setDate(saturdayDate.getDate() + saturday);
    console.log(curr);
    console.log(saturdayDate);

    let month = [
      "Januari",
      "Februari",
      "Maret",
      "April",
      "Mei",
      "Juni",
      "July",
      "Agustus",
      "September",
      "Oktober",
      "November",
      "December",
    ];

    $("#rentang-waktu").html(
      `${sundayDate.getDate()} ${
        month[sundayDate.getMonth()]
      } ${sundayDate.getFullYear()} - ${saturdayDate.getDate()} ${
        month[saturdayDate.getMonth()]
      } ${saturdayDate.getFullYear()}`
    );

    bar(data_pelaporan_mingguan, data_pelaporan_darurat_mingguan, [
      "Mingu",
      "Senin",
      "Selasa",
      "Rabu",
      "Kamis",
      "Jumat",
      "Sabtu",
    ]);
  } else {
    read_analytic();
  }
};

const read_analytic_month = async () => {
  const idAkomodasi = localStorage.getItem("id_akomodasi");
  const req = await fetch(
    `https://sipanduberadat.com/api/admin-akomodasi/analytic/?id_akomodasi=${idAkomodasi}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    $("#total-pegawai").html(data.total_pegawai);
    $("#total-e-kulkul").html(data.total_sirine);
    $("#total-wisatawan").html(data.total_tamu);
    $("#total-daftar-hitam").html(data.total_block);
    $("#total-pelaporan").html(data.total_pelaporan);
    $("#total-pelaporan-hari-ini").html(data.total_today_pelaporan);
    $("#total-admin").html(data.total_admin);
    temp = data.this_month_pelaporan;
    data_pelaporan_bulanan = Object.values(temp);
    // data_pelaporan_bulanan = Array(data_pelaporan_bulanan.length).fill(5);
    temp = data.this_month_pelaporan_darurat;
    data_pelaporan_darurat_bulanan = Object.values(temp);
    // total_data_pelaporan = data_pelaporan_darurat_tahunan.map(function (num, idx) {
    //   return num + data_pelaporan_tahunan[idx];
    // });
    let total_pelaporan = 0;
    let total_pelaporan_darurat = 0;
    $.each(data_pelaporan_bulanan, function () {
      total_pelaporan += this || 0;
    });
    $.each(data_pelaporan_darurat_bulanan, function () {
      total_pelaporan_darurat += this || 0;
    });
    $("#total-pelaporan-darurat").html(total_pelaporan_darurat);
    $("#total-keluhan").html(total_pelaporan);

    var curr;
    curr = new Date();
    var sundayDate;
    sundayDate = new Date();
    var sunday;
    sunday = 0 - curr.getDay();
    sundayDate.setDate(sundayDate.getDate() + sunday);
    console.log(curr);
    console.log(sundayDate);
    var saturdayDate;
    saturdayDate = new Date();
    var saturday;
    saturday = 6 - curr.getDay();
    saturdayDate.setDate(saturdayDate.getDate() + saturday);
    console.log(curr);
    console.log(saturdayDate);

    let month = [
      "Januari",
      "Februari",
      "Maret",
      "April",
      "Mei",
      "Juni",
      "July",
      "Agustus",
      "September",
      "Oktober",
      "November",
      "December",
    ];

    $("#rentang-waktu").html(`${month[sundayDate.getMonth()]}`);
    console.log(total_data_pelaporan);
    areaChart(
      data_pelaporan_bulanan,
      data_pelaporan_darurat_bulanan,
      Object.keys(temp)
    );
  } else {
    read_analytic_month();
  }
};

const read_analytic_year = async () => {
  const idAkomodasi = localStorage.getItem("id_akomodasi");
  const req = await fetch(
    `https://sipanduberadat.com/api/admin-akomodasi/analytic/?id_akomodasi=${idAkomodasi}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    $("#total-pegawai").html(data.total_pegawai);
    $("#total-e-kulkul").html(data.total_sirine);
    $("#total-wisatawan").html(data.total_tamu);
    $("#total-daftar-hitam").html(data.total_block);
    $("#total-pelaporan").html(data.total_pelaporan);
    $("#total-pelaporan-hari-ini").html(data.total_today_pelaporan);
    $("#total-admin").html(data.total_admin);
    temp = data.this_year_pelaporan;
    data_pelaporan_tahunan = [
      temp.Januari,
      temp.Februari,
      temp.Maret,
      temp.April,
      temp.Mei,
      temp.Juni,
      temp.Juli,
      temp.Agustus,
      temp.September,
      temp.Oktober,
      temp.November,
      temp.Desember,
    ];
    temp = data.this_year_pelaporan_darurat;
    data_pelaporan_darurat_tahunan = [
      temp.Januari,
      temp.Februari,
      temp.Maret,
      temp.April,
      temp.Mei,
      temp.Juni,
      temp.Juli,
      temp.Agustus,
      temp.September,
      temp.Oktober,
      temp.November,
      temp.Desember,
    ];
    total_data_pelaporan = data_pelaporan_darurat_tahunan.map(function (
      num,
      idx
    ) {
      return num + data_pelaporan_tahunan[idx];
    });
    total_data_pelaporan = [5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5];
    let total_pelaporan = 0;
    let total_pelaporan_darurat = 0;
    $.each(data_pelaporan_tahunan, function () {
      total_pelaporan += this || 0;
    });
    $.each(data_pelaporan_darurat_tahunan, function () {
      total_pelaporan_darurat += this || 0;
    });
    $("#total-pelaporan-darurat").html(total_pelaporan_darurat);
    $("#total-keluhan").html(total_pelaporan);

    var curr;
    curr = new Date();
    var sundayDate;
    sundayDate = new Date();
    var sunday;
    sunday = 0 - curr.getDay();
    sundayDate.setDate(sundayDate.getDate() + sunday);
    console.log(curr);
    console.log(sundayDate);
    var saturdayDate;
    saturdayDate = new Date();
    var saturday;
    saturday = 6 - curr.getDay();
    saturdayDate.setDate(saturdayDate.getDate() + saturday);
    console.log(curr);
    console.log(saturdayDate);

    let month = [
      "Januari",
      "Februari",
      "Maret",
      "April",
      "Mei",
      "Juni",
      "July",
      "Agustus",
      "September",
      "Oktober",
      "November",
      "December",
    ];

    $("#rentang-waktu").html(`${sundayDate.getFullYear()}`);
    console.log(total_data_pelaporan);
    bar(data_pelaporan_tahunan, data_pelaporan_darurat_tahunan, [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "Mei",
      "Jun",
      "Jul",
      "Agu",
      "Sep",
      "Okt",
      "Nov",
      "Des",
    ]);
  } else {
    read_analytic_tahunan();
  }
};

const bar = (data_pelaporan, data_pelaporan_darurat, labels) => {
  Chart.elements.Rectangle.prototype.draw = function () {
    var ctx = this._chart.ctx;
    var vm = this._view;
    var left, right, top, bottom, signX, signY, borderSkipped, radius;
    var borderWidth = vm.borderWidth;
    // Set Radius Here
    // If radius is large enough to cause drawing errors a max radius is imposed
    var cornerRadius = 10;

    if (!vm.horizontal) {
      // bar
      left = vm.x - vm.width / 2;
      right = vm.x + vm.width / 2;
      top = vm.y;
      bottom = vm.base;
      signX = 1;
      signY = bottom > top ? 1 : -1;
      borderSkipped = vm.borderSkipped || "bottom";
    } else {
      // horizontal bar
      left = vm.base;
      right = vm.x;
      top = vm.y - vm.height / 2;
      bottom = vm.y + vm.height / 2;
      signX = right > left ? 1 : -1;
      signY = 1;
      borderSkipped = vm.borderSkipped || "left";
    }

    // Canvas doesn't allow us to stroke inside the width so we can
    // adjust the sizes to fit if we're setting a stroke on the line
    if (borderWidth) {
      // borderWidth shold be less than bar width and bar height.
      var barSize = Math.min(Math.abs(left - right), Math.abs(top - bottom));
      borderWidth = borderWidth > barSize ? barSize : borderWidth;
      var halfStroke = borderWidth / 2;
      // Adjust borderWidth when bar top position is near vm.base(zero).
      var borderLeft =
        left + (borderSkipped !== "left" ? halfStroke * signX : 0);
      var borderRight =
        right + (borderSkipped !== "right" ? -halfStroke * signX : 0);
      var borderTop = top + (borderSkipped !== "top" ? halfStroke * signY : 0);
      var borderBottom =
        bottom + (borderSkipped !== "bottom" ? -halfStroke * signY : 0);
      // not become a vertical line?
      if (borderLeft !== borderRight) {
        top = borderTop;
        bottom = borderBottom;
      }
      // not become a horizontal line?
      if (borderTop !== borderBottom) {
        left = borderLeft;
        right = borderRight;
      }
    }

    ctx.beginPath();
    ctx.fillStyle = vm.backgroundColor;
    ctx.strokeStyle = vm.borderColor;
    ctx.lineWidth = borderWidth;

    // Corner points, from bottom-left to bottom-right clockwise
    // | 1 2 |
    // | 0 3 |
    var corners = [
      [left, bottom],
      [left, top],
      [right, top],
      [right, bottom],
    ];

    // Find first (starting) corner with fallback to 'bottom'
    var borders = ["bottom", "left", "top", "right"];
    var startCorner = borders.indexOf(borderSkipped, 0);
    if (startCorner === -1) {
      startCorner = 0;
    }

    function cornerAt(index) {
      return corners[(startCorner + index) % 4];
    }

    // Draw rectangle from 'startCorner'
    var corner = cornerAt(0);
    var width, height, x, y, nextCorner, nextCornerId;
    ctx.moveTo(corner[0], corner[1]);

    for (var i = 1; i < 4; i++) {
      corner = cornerAt(i);
      nextCornerId = i + 1;
      if (nextCornerId == 4) {
        nextCornerId = 0;
      }

      nextCorner = cornerAt(nextCornerId);

      width = corners[2][0] - corners[1][0];
      height = corners[0][1] - corners[1][1];
      x = corners[1][0];
      y = corners[1][1];

      var radius = cornerRadius;

      // Fix radius being too large
      if (radius > height / 2) {
        radius = height / 2;
      }
      if (radius > width / 2) {
        radius = width / 2;
      }

      ctx.moveTo(x + radius, y);
      ctx.lineTo(x + width - radius, y);
      ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
      ctx.lineTo(x + width, y + height - radius);
      ctx.quadraticCurveTo(
        x + width,
        y + height,
        x + width - radius,
        y + height
      );
      ctx.lineTo(x + radius, y + height);
      ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
      ctx.lineTo(x, y + radius);
      ctx.quadraticCurveTo(x, y, x + radius, y);
    }

    ctx.fill();
    if (borderWidth) {
      ctx.stroke();
    }
  };
  // var total_data_pelaporan = window.parent.total_data_pelaporan;
  // console.log(total_data_pelaporan);
  max = Math.max.apply(null, total_data_pelaporan) + 5;
  maxs = Array(total_data_pelaporan.length).fill(max);
  console.log(maxs);
  var dataBar = {
    labels: labels,
    datasets: [
      {
        data: data_pelaporan_darurat,
        backgroundColor: "#D62D2e",
        borderColor: "#D62D2e",
        pointRadius: 0,
        lineTension: 0,
        borderWidth: 1,
        label: "Darurat",
      },
      {
        data: data_pelaporan,
        backgroundColor: "#3f50f6",
        borderColor: "#3f50f6",
        pointRadius: 0,
        lineTension: 0,
        borderWidth: 1,
        label: "Keluhan",
      },
      // {
      //   data: maxs,
      //   backgroundColor: "#e6e6e6",
      //   borderColor: "#e6e6e6",
      //   pointRadius: 0,
      //   lineTension: 0,
      //   borderWidth: 1,
      // },
    ],
  };
  var options = {
    responsive: true,
    legend: {
      display: true,
    },
    barRoundness: 1,
    scales: {
      xAxes: [
        {
          display: true,
          gridLines: {
            display: false,
            drawBorder: false,
          },
          barPercentage: 0.7,
        },
      ],
      yAxes: [
        {
          ticks: {
            display: false,
            beginAtZero: true,
          },
          display: true,
          gridLines: {
            display: false,
            drawBorder: false,
          },
        },
      ],
    },
  };
  $("#surveyBar").remove(); // this is my <canvas> element
  $("#bagian-bar").append(
    '<canvas id="surveyBar" style="width: 100%; height:310px"></canvas>'
  );
  var ctxBar = document.getElementById("surveyBar");
  var myBarChart = new Chart(ctxBar, {
    type: "bar",
    data: dataBar,
    options: options,
  });
};

const areaChart = (data_pelaporan, data_pelaporan_darurat, labels) => {
  var areaData2 = {
    labels: labels,
    datasets: [
      {
        label: "Keluhan",
        data: data_pelaporan,
        backgroundColor: ["#3f50f6"],
        borderColor: ["#3f50f6"],
        borderWidth: 3,
        pointRadius: 0,
        fill: false, // 3: no fill
      },
      {
        label: "Darurat",
        data: data_pelaporan_darurat,
        backgroundColor: ["#D62D2e"],
        borderColor: ["#D62D2e"],
        borderWidth: 3,
        pointRadius: 0,
        fill: false, // 3: no fill
      },
    ],
  };
  var areaOptions2 = {
    scales: {
      yAxes: [
        {
          ticks: {
            beginAtZero: true,
            display: true,
          },
          gridLines: {
            drawBorder: false,
            display: false,
          },
        },
      ],
      xAxes: [
        {
          gridLines: {
            drawBorder: false,
            display: false,
          },
        },
      ],
    },
    legend: {
      display: true,
    },
    tooltips: {
      cornerRadius: 4,
      caretSize: 4,
      xPadding: 16,
      yPadding: 10,
      backgroundColor: "rgba(0, 150, 100, 0.9)",
      titleFontStyle: "normal",
      titleMarginBottom: 15,
    },
  };
  $("#surveyBar").remove(); // this is my <canvas> element
  $("#bagian-bar").append(
    '<canvas id="surveyBar" width="160" height="80" style="display: block; height: 64px; width: 128px;" class="chartjs-render-monitor"></canvas>'
  );
  var areaChartCanvas = document.getElementById("surveyBar");
  var areaChart = new Chart(areaChartCanvas, {
    type: "line",
    data: areaData2,
    options: areaOptions2,
  });
};
