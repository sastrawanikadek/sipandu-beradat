$(document).ready(function () {
  const charts = ["chartWeek", "chartMonth", "chartYear"];
  const buttons = ["btn-week", "btn-month", "btn-year"];

  charts.map((v, i) => i !== 0 && $(`#${v}`).hide());

  buttons.map((v, i) =>
    $(`#${v}`).click((e) => {
      $(".btn-toggle-chart").removeClass("btn-primary");
      $(e.currentTarget).addClass("btn-primary");
      charts.map((v, j) => (j === i ? $(`#${v}`).show() : $(`#${v}`).hide()));
    })
  );

  readChart();
});

const readChart = async () => {
  const req = await fetch(
    `https://sipanduberadat.com/api/superadmin/analytic/`,
    {
      method: "GET",
    }
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    generateChart(
      "chartYear",
      data.this_year_pelaporan,
      data.this_year_pelaporan_darurat
    );
    generateChart(
      "chartMonth",
      data.this_month_pelaporan,
      data.this_month_pelaporan_darurat
    );
    generateChart(
      "chartWeek",
      data.this_week_pelaporan,
      data.this_week_pelaporan_darurat
    );
  } else if (status_code === 401) {
    readChart();
  }
};

const generateChart = (id, keluhan, darurat) => {
  const data = {
    labels: Object.keys(darurat),
    datasets: [
      {
        label: "Pelaporan Keluhan",
        data: Object.values(keluhan),
        backgroundColor: ["#d62d2d38"],
        borderColor: ["#d62d2e"],
        borderWidth: 3,
        fill: true,
        tension: 0.1,
        pointStyle: "circle",
        pointRadius: 3,
      },
      {
        label: "Pelaporan Darurat",
        data: Object.values(darurat),
        backgroundColor: ["#198be344"],
        borderColor: ["#198ae3"],
        borderWidth: 3,
        fill: true,
        tension: 0.1,
        pointStyle: "circle",
        pointRadius: 3,
      },
    ],
  };

  const options = {
    animations: {
      y: {
        easing: "easeInOutElastic",
        from: (ctx) => {
          if (ctx.type === "data") {
            if (ctx.mode === "default" && !ctx.dropped) {
              ctx.dropped = true;
              return 0;
            }
          }
        },
      },
    },
    interaction: {
      intersect: false,
      mode: "index",
    },
    responsive: true,
    scales: {
      x: {
        grid: {
          display: false,
          drawBorder: false,
        },
      },
      y: {
        grid: {
          display: true,
          drawBorder: false,
        },
        ticks: {
          beginAtZero: true,
          stepSize: 1,
        },
      },
    },
    plugins: {
      tooltip: {
        mode: "index",
        intersect: false,
      },
      legend: {
        display: false,
      },
    },
  };

  if ($(`#${id}`).length) {
    const ctx = $(`#${id}`);
    new Chart(ctx, {
      type: "line",
      data: data,
      options: options,
    });
  }
};
