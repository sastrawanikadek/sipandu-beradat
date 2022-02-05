package com.sipanduberadat.guest.activities

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.View
import androidx.recyclerview.widget.LinearLayoutManager
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.adapters.HistoryAdapter
import com.sipanduberadat.guest.models.PelaporanTamu
import com.sipanduberadat.guest.models.Tamu
import com.sipanduberadat.guest.services.apis.findAllTamuEmergencyReportAPI
import com.sipanduberadat.guest.services.apis.findAllTamuNotEmergencyReportAPI
import kotlinx.android.synthetic.main.activity_report_history.*

class ReportHistoryActivity : AppCompatActivity() {
    private lateinit var me: Tamu
    private val histories: MutableList<PelaporanTamu> = mutableListOf()

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessHistoryReport(response: Any?) {
        if (response != null) {
            shimmer_container.stopShimmer()
            shimmer_container.visibility = View.GONE
            recycler_view.visibility = View.VISIBLE

            histories.addAll((response as Array<PelaporanTamu>).toList())
            histories.sortByDescending { it.time.time }
            recycler_view.adapter?.notifyDataSetChanged()
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessHistoryEmergencyReport(response: Any?) {
        if (response != null) {
            histories.clear()
            histories.addAll((response as Array<PelaporanTamu>).toList())

            val requestParams = HashMap<String, String>()
            requestParams["id_masyarakat"] = "${me.id}"
            findAllTamuNotEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessHistoryReport,
                    this::onRequestError, showMessage = false)
        }
    }

    private fun onRequestError() {}

    private fun onRequestReport() {
        histories.clear()
        recycler_view.adapter?.notifyDataSetChanged()
        recycler_view.visibility = View.GONE
        shimmer_container.visibility = View.VISIBLE
        shimmer_container.startShimmer()

        val requestParams = HashMap<String, String>()
        requestParams["id_masyarakat"] = "${me.id}"
        findAllTamuEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessHistoryEmergencyReport,
                this::onRequestError, showMessage = false)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_report_history)

        me = intent.getParcelableExtra("ME")!!

        recycler_view.apply {
            layoutManager = LinearLayoutManager(this@ReportHistoryActivity,
                    LinearLayoutManager.VERTICAL, false)
            adapter = HistoryAdapter(this@ReportHistoryActivity, histories)
        }

        btn_back.setOnClickListener { finish() }
        swipe_refresh.setOnRefreshListener {
            Handler(Looper.getMainLooper()).postDelayed({
                swipe_refresh.isRefreshing = false
                onRequestReport()
            }, 300)
        }

        onRequestReport()
    }
}