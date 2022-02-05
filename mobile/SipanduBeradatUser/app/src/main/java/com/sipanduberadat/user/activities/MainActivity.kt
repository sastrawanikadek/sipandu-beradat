package com.sipanduberadat.user.activities

import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import androidx.lifecycle.ViewModelProvider
import com.sipanduberadat.user.R
import com.sipanduberadat.user.adapters.MainViewPagerAdapter
import com.sipanduberadat.user.models.*
import com.sipanduberadat.user.services.apis.*
import com.sipanduberadat.user.viewModels.MainViewModel
import kotlinx.android.synthetic.main.activity_main.*

class MainActivity : AppCompatActivity() {
    private lateinit var viewModel: MainViewModel
    private var reportHistories: MutableList<Pelaporan> = mutableListOf()
    private var reports: MutableList<Pelaporan> = mutableListOf()
    private var guestReports: MutableList<PelaporanTamu> = mutableListOf()

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessBlockedRoads(response: Any?) {
        if (response != null) {
            val blockedRoads = (response as Array<PenutupanJalan>).toList()
            viewModel.blockedRoads.value = blockedRoads
        }
    }

    private fun onRequestBlockedRoads() {
        if (viewModel.me.value == null) {
            Handler(Looper.getMainLooper()).postDelayed({
                onRequestNews()
            }, 300)
            return
        }
        val requestParams = HashMap<String, String>()
        requestParams["id_desa"] = "${viewModel.me.value!!.masyarakat.banjar.desa_adat.id}"
        findAllPenutupanJalanAPI(root, this, requestParams, HashMap(), this::onSuccessBlockedRoads,
                this::onRequestError, showMessage = false)
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessNews(response: Any?) {
        if (response != null) {
            val news = (response as Array<Berita>).toList()
            viewModel.news.value = news.sortedByDescending { it.time.time }
        }
    }

    private fun onRequestNews() {
        if (viewModel.me.value == null) {
            Handler(Looper.getMainLooper()).postDelayed({
                onRequestNews()
            }, 300)
            return
        }
        val requestParams = HashMap<String, String>()
        requestParams["id_desa_adat"] = "${viewModel.me.value!!.masyarakat.banjar.desa_adat.id}"
        findAllBeritaDesaAdatAPI(root, this, requestParams, HashMap(), this::onSuccessNews,
                this::onRequestError, showMessage = false)
    }

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
            requestParams["id_desa"] = "${viewModel.me.value!!.masyarakat.banjar.desa_adat.id}"
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
        requestParams["id_desa"] = "${viewModel.me.value!!.masyarakat.banjar.desa_adat.id}"
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
            requestParams["id_desa"] = "${viewModel.me.value!!.masyarakat.banjar.desa_adat.id}"
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
        requestParams["id_desa"] = "${viewModel.me.value!!.masyarakat.banjar.desa_adat.id}"
        findAllEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessEmergencyReport,
                this::onRequestError, showMessage = false)
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessRequestFamily(response: Any?) {
        if (response != null) {
            viewModel.requestFamilies.value = (response as Array<Kerabat>).toList()
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessFamily(response: Any?) {
        if (response != null) {
            viewModel.families.value = (response as Array<Kerabat>).toList()
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessHistoryReport(response: Any?) {
        if (response != null) {
            val histories = (response as Array<Pelaporan>).toList()
            reportHistories.addAll(histories)
            viewModel.reportHistories.value = (if (reportHistories.size > 5)
                reportHistories.sortedByDescending { it.time.time }.subList(0, 5) else
                    reportHistories.sortedByDescending { it.time.time })
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessHistoryEmergencyReport(response: Any?) {
        if (response != null) {
            val histories = (response as Array<Pelaporan>).toList()
            reportHistories.clear()
            reportHistories.addAll(histories)

            val requestParams = HashMap<String, String>()
            requestParams["id_masyarakat"] = "${viewModel.me.value!!.masyarakat.id}"
            findAllNotEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessHistoryReport,
                    this::onRequestError, showMessage = false)
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessReportType(response: Any?) {
        if (response != null) {
            viewModel.reportTypes.value = (response as Array<JenisPelaporan>).toList()
        }
    }

    private fun onSuccessMe(response: Any?) {
        if (response != null) {
            viewModel.me.value = response as Me

            if (viewModel.me.value!!.pecalang != null) {
                bottom_navigation.menu.findItem(R.id.report).isVisible = true
            }

            for (i in 0 until bottom_navigation.menu.size()) {
                bottom_navigation.menu.getItem(i).isEnabled = true
            }

            if (intent.hasExtra("NOTIFICATION_TYPE")) {
                when (intent.getStringExtra("NOTIFICATION_TYPE")) {
                    "family-request" -> {
                        val profileIntent = Intent(this, ProfileKramaActivity::class.java)
                        profileIntent.putExtra("ME", viewModel.me.value!!)
                        profileIntent.putExtra("KRAMA_ID",
                                intent.getStringExtra("NOTIFICATION_ACTION_ID"))
                        startActivity(profileIntent)
                        intent.removeExtra("NOTIFICATION_TYPE")
                        intent.removeExtra("NOTIFICATION_ACTION_ID")
                    }
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

    private fun onRequestHistoryReport() {
        if (viewModel.me.value == null) {
            Handler(Looper.getMainLooper()).postDelayed({
                onRequestHistoryReport()
            }, 300)
            return
        }
        val requestParams = HashMap<String, String>()
        requestParams["id_masyarakat"] = "${viewModel.me.value!!.masyarakat.id}"
        findAllEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessHistoryEmergencyReport,
            this::onRequestError, showMessage = false)
    }

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
                    onRequestHistoryReport()
                }
                1 -> {
                    onRequestReport()
                    onRequestGuestReport()
                }
                2 -> {
                    onRequestBlockedRoads()
                    onRequestNews()
                    onRequestReport()
                    onRequestGuestReport()
                }
                3 -> {
                    findAllKerabatAPI(root, this, HashMap(), HashMap(), this::onSuccessFamily,
                            this::onRequestError, showMessage = false)
                    findAllRequestKerabatAPI(root, this, HashMap(), HashMap(),
                            this::onSuccessRequestFamily, this::onRequestError, showMessage = false)
                }
            }
            true
        }
        bottom_navigation.selectedItemId = R.id.home

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

        viewModel.reportHistories.observe(this, { if (it == null) onRequestHistoryReport() })
        viewModel.reports.observe(this, { if (it == null) onRequestReport() })
        viewModel.guestReports.observe(this, { if (it == null) onRequestGuestReport() })
        viewModel.news.observe(this, { if (it == null) onRequestNews() })
        viewModel.blockedRoads.observe(this, { if (it == null) onRequestBlockedRoads() })

        viewModel.families.observe(this, {
            if (it == null) {
                findAllKerabatAPI(root, this, HashMap(), HashMap(), this::onSuccessFamily,
                        this::onRequestError, showMessage = false)
            }
        })

        viewModel.requestFamilies.observe(this, {
            if (it == null) {
                findAllRequestKerabatAPI(root, this, HashMap(), HashMap(), this::onSuccessRequestFamily,
                        this::onRequestError, showMessage = false)
            }
        })
    }
}