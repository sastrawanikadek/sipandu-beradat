package com.sipanduberadat.petugas.activities

import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import androidx.lifecycle.ViewModelProvider
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.adapters.MainViewPagerAdapter
import com.sipanduberadat.petugas.models.*
import com.sipanduberadat.petugas.services.apis.*
import com.sipanduberadat.petugas.viewModels.MainViewModel
import kotlinx.android.synthetic.main.activity_main.*

class MainActivity : AppCompatActivity() {
    private lateinit var viewModel: MainViewModel
    private var reports: MutableList<Pelaporan> = mutableListOf()
    private var guestReports: MutableList<PelaporanTamu> = mutableListOf()

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessGuestReport(response: Any?) {
        if (response != null) {
            val reportData = (response as Array<PelaporanTamu>).toList()
            guestReports.addAll(reportData)
            viewModel.guestReports.value = guestReports.sortedByDescending { it.time.time }
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessGuestEmergencyReport(response: Any?) {
        if (response != null) {
            val reportData = (response as Array<PelaporanTamu>).toList()
            guestReports.clear()
            guestReports.addAll(reportData)

            val requestParams = HashMap<String, String>()
            requestParams["id_instansi"] = "${viewModel.me.value!!.instansi_petugas.id}"
            findAllTamuNotEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessGuestReport,
                    this::onRequestError, showMessage = false)
        }
    }

    private fun onRequestGuestReport() {
        if (viewModel.me.value == null) {
            Handler(Looper.getMainLooper()).postDelayed({
                onRequestGuestReport()
            }, 300)
            return
        }
        val requestParams = HashMap<String, String>()
        requestParams["id_instansi"] = "${viewModel.me.value!!.instansi_petugas.id}"
        findAllTamuEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessGuestEmergencyReport,
                this::onRequestError, showMessage = false)
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessReport(response: Any?) {
        if (response != null) {
            val reportData = (response as Array<Pelaporan>).toList()
            reports.addAll(reportData)
            viewModel.reports.value = reports.sortedByDescending { it.time.time }
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessEmergencyReport(response: Any?) {
        if (response != null) {
            val reportData = (response as Array<Pelaporan>).toList()
            reports.clear()
            reports.addAll(reportData)

            val requestParams = HashMap<String, String>()
            requestParams["id_instansi"] = "${viewModel.me.value!!.instansi_petugas.id}"
            findAllNotEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessReport,
                    this::onRequestError, showMessage = false)
        }
    }

    private fun onRequestReport() {
        if (viewModel.me.value == null) {
            Handler(Looper.getMainLooper()).postDelayed({
                onRequestReport()
            }, 300)
            return
        }
        val requestParams = HashMap<String, String>()
        requestParams["id_instansi"] = "${viewModel.me.value!!.instansi_petugas.id}"
        findAllEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessEmergencyReport,
                this::onRequestError, showMessage = false)
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessReportType(response: Any?) {
        if (response != null) {
            viewModel.reportTypes.value = (response as Array<JenisPelaporan>).toList()
        }
    }

    private fun onSuccessMe(response: Any?) {
        if (response != null) {
            viewModel.me.value = response as Petugas

            for (i in 0 until bottom_navigation.menu.size()) {
                bottom_navigation.menu.getItem(i).isEnabled = true
            }

            if (intent.hasExtra("NOTIFICATION_TYPE")) {
                when (intent.getStringExtra("NOTIFICATION_TYPE")) {
                    "report" -> {
                        val reportIntent = Intent(this, ReportDetailActivity::class.java)
                        reportIntent.putExtra("ME", viewModel.me.value!!)
                        reportIntent.putExtra("REPORT_ID",
                            intent.getLongExtra("NOTIFICATION_ACTION_ID", -1))
                        reportIntent.putExtra("REPORT_EMERGENCY_STATUS", false)
                        reportIntent.putExtra("IS_REPORTER_KRAMA", true)
                        startActivity(reportIntent)
                        intent.removeExtra("NOTIFICATION_TYPE")
                        intent.removeExtra("NOTIFICATION_ACTION_ID")
                    }
                    "emergency-report" -> {
                        val reportIntent = Intent(this, ReportDetailActivity::class.java)
                        reportIntent.putExtra("ME", viewModel.me.value!!)
                        reportIntent.putExtra("REPORT_ID",
                            intent.getLongExtra("NOTIFICATION_ACTION_ID", -1))
                        reportIntent.putExtra("REPORT_EMERGENCY_STATUS", true)
                        reportIntent.putExtra("IS_REPORTER_KRAMA", true)
                        startActivity(reportIntent)
                        intent.removeExtra("NOTIFICATION_TYPE")
                        intent.removeExtra("NOTIFICATION_ACTION_ID")
                    }
                    "guest-report" -> {
                        val reportIntent = Intent(this, ReportDetailActivity::class.java)
                        reportIntent.putExtra("ME", viewModel.me.value!!)
                        reportIntent.putExtra("REPORT_ID",
                                intent.getLongExtra("NOTIFICATION_ACTION_ID", -1))
                        reportIntent.putExtra("REPORT_EMERGENCY_STATUS", false)
                        reportIntent.putExtra("IS_REPORTER_KRAMA", false)
                        startActivity(reportIntent)
                        intent.removeExtra("NOTIFICATION_TYPE")
                        intent.removeExtra("NOTIFICATION_ACTION_ID")
                    }
                    "guest-emergency-report" -> {
                        val reportIntent = Intent(this, ReportDetailActivity::class.java)
                        reportIntent.putExtra("ME", viewModel.me.value!!)
                        reportIntent.putExtra("REPORT_ID",
                                intent.getLongExtra("NOTIFICATION_ACTION_ID", -1))
                        reportIntent.putExtra("REPORT_EMERGENCY_STATUS", true)
                        reportIntent.putExtra("IS_REPORTER_KRAMA", false)
                        startActivity(reportIntent)
                        intent.removeExtra("NOTIFICATION_TYPE")
                        intent.removeExtra("NOTIFICATION_ACTION_ID")
                    }
                }
            }
        }
    }

    private fun onRequestError() {}

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        viewModel = ViewModelProvider(this).get(MainViewModel::class.java)

        view_pager.adapter = MainViewPagerAdapter(supportFragmentManager)
        bottom_navigation.setOnNavigationItemSelectedListener {
            view_pager.currentItem = it.order

            when (view_pager.currentItem) {
                0 -> {
                    meAPI(root, this, HashMap(), HashMap(), this::onSuccessMe, this::onRequestError,
                        showMessage = false)
                    findAllJenisPelaporanAPI(root, this, HashMap(), HashMap(), this::onSuccessReportType,
                        this::onRequestError, showMessage = false)
                    onRequestReport()
                    onRequestGuestReport()
                }
            }
            true
        }
        bottom_navigation.selectedItemId = R.id.report

        for (i in 0 until bottom_navigation.menu.size()) {
            bottom_navigation.menu.getItem(i).isEnabled = false
        }

        viewModel.me.observe(this, {
            if (it == null) {
                for (i in 0 until bottom_navigation.menu.size()) {
                    bottom_navigation.menu.getItem(i).isEnabled = false
                }

                meAPI(root, this, HashMap(), HashMap(), this::onSuccessMe, this::onRequestError,
                    showMessage = false)
            }
        })

        viewModel.reportTypes.observe(this, {
            if (it == null) {
                findAllJenisPelaporanAPI(root, this, HashMap(), HashMap(), this::onSuccessReportType,
                    this::onRequestError, showMessage = false)
            }
        })

        viewModel.reports.observe(this, { if (it == null) onRequestReport() })
        viewModel.guestReports.observe(this, { if (it == null) onRequestGuestReport() })
    }
}