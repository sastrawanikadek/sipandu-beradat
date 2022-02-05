$(document).ready(async () => {
  const charts = ["chartWeek", "chartMonth", "chartYear"];
  const buttons = ["btn-week", "btn-month", "btn-year"];

  charts.map((v, i) => i !== 0 && $(`#${v}`).hide());
  buttons.map((v, i) => $(`#${v}`).click((e) => {
    $(".btn-toggle-chart").removeClass("btn-primary");
    $(e.currentTarget).addClass("btn-primary");
    charts.map((v, j) => j === i ? $(`#${v}`).show() : $(`#${v}`).hide());
  }))

  totalAnalytics();
});

const generateChart = (id, pelaporan, pelaporan_darurat) => {
  const darurat = 'rgba(252, 84, 75, 1)'
    const keluhan = 'rgba(78, 115, 223, 1)'
    const areaData = {
      labels: Object.keys(pelaporan),
      datasets: [{
        label: 'Pelaporan Keluhan',
        data: Object.values(pelaporan),
        lineTension: 0.3,
        backgroundColor: "rgba(78, 115, 223, 0.05)",
        borderColor: keluhan,
        pointRadius: 3,
        pointBackgroundColor: keluhan,
        pointBorderColor: keluhan,
        pointHoverRadius: 3,
        pointHoverBackgroundColor: keluhan,
        pointHoverBorderColor: keluhan,
        pointHitRadius: 10,
        pointBorderWidth: 2,
      }, {

        label: 'Pelaporan Darurat',
        data: Object.values(pelaporan_darurat),
        lineTension: 0.3,
        backgroundColor: "rgba(252, 84, 75, 0.05)",
        borderColor: darurat,
        pointRadius: 3,
        pointBackgroundColor: darurat,
        pointBorderColor: darurat,
        pointHoverRadius: 3,
        pointHoverBackgroundColor: darurat,
        pointHoverBorderColor: darurat,
        pointHitRadius: 10,
        pointBorderWidth: 2,
      }]
    };

    const areaOptions = {
      plugins: {
        filler: {
          propagate: true
        }
      },
      layout: {
        padding: {
          left: 10,
          right: 25,
          top: 25,
          bottom: 0
        }
      },
      scales: {
        xAxes: [{
          time: {
            unit: 'date'
          },
          gridLines: {
            display: false,
            drawBorder: false
          },
          ticks: {
            maxTicksLimit: 7
          }
        }],
        yAxes: [{
          ticks: {
            maxTicksLimit: 5,
            padding: 10,
          },
          gridLines: {
            color: "rgb(234, 236, 244)",
            zeroLineColor: "rgb(234, 236, 244)",
            drawBorder: false,
            borderDash: [2],
            zeroLineBorderDash: [2]
          }
        }],
      },
      legend: {
        display: false
      },
    }

    if ($(`#${id}`).length) {
      const areaChartCanvas = $(`#${id}`).get(0).getContext("2d");
      new Chart(areaChartCanvas, {
        type: 'line',
        data: areaData,
        options: areaOptions
      });
    }
}

const totalAnalytics = async () => {
  const idDesa = localStorage.getItem("id_desa")
  const req = await fetch(`https://api-sipandu-beradat.000webhostapp.com/admin-desa-adat/analytic/?id_desa=${idDesa}`, {
    method: "GET"
  });
  const {
    status_code,
    data
  } = await req.json();

  if (status_code === 200) {
    $("#total-pecalang").html(data.total_pecalang)
    $("#total-e-kulkul").html(data.total_sirine)
    $("#total-krama-desa").html(data.total_masyarakat)
    $("#total-today-pelaporan").html(data.total_today_pelaporan)
    $("#total-block").html(data.total_block)
    $("#total-banjar").html(data.total_banjar)

    $("#total-krama-wid").html(data.masyarakat_categories["Krama Wid"] ?? 0)
    $("#total-krama-tamiu").html(data.masyarakat_categories["Krama Tamiu"] ?? 0)
    $("#total-tamiu").html(data.masyarakat_categories["Tamiu"] ?? 0)

    $("#progress-krama-wid").css("width", `${(data.masyarakat_categories["Krama Wid"] ?? 0) / data.total_masyarakat * 100}%`)
    $("#progress-krama-tamiu").css("width", `${(data.masyarakat_categories["Krama Tamiu"] ?? 0) / data.total_masyarakat * 100}%`)
    $("#progress-tamiu").css("width", `${(data.masyarakat_categories["Tamiu"] ?? 0) / data.total_masyarakat * 100}%`)

    generateChart("chartYear", data.this_year_pelaporan, data.this_year_pelaporan_darurat)
    generateChart("chartMonth", data.this_month_pelaporan, data.this_month_pelaporan_darurat)
    generateChart("chartWeek", data.this_week_pelaporan, data.this_week_pelaporan_darurat)
    

  } else if (status_code === 401) {
    totalAnalytics()
  }
};
