package com.sipanduberadat.user.activities

import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.View
import android.widget.LinearLayout
import androidx.lifecycle.ViewModelProvider
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.LatLng
import com.google.android.gms.maps.model.MarkerOptions
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.sipanduberadat.user.R
import com.sipanduberadat.user.adapters.ReportDetailViewPagerAdapter
import com.sipanduberadat.user.dialogs.ReportBottomSheetDialog
import com.sipanduberadat.user.models.Me
import com.sipanduberadat.user.models.Pelaporan
import com.sipanduberadat.user.models.PelaporanTamu
import com.sipanduberadat.user.services.apis.*
import com.sipanduberadat.user.utils.Constants
import com.sipanduberadat.user.utils.getViewport
import com.sipanduberadat.user.viewModels.ReportDetailViewModel
import kotlinx.android.synthetic.main.activity_report_detail.*

class ReportDetailActivity : AppCompatActivity() {
    private lateinit var viewModel: ReportDetailViewModel
    private lateinit var map: GoogleMap
    private lateinit var me: Me
    private var id: Long = -1
    private var isEmergency: Boolean = false
    private var isKrama: Boolean = true

    private fun onSuccessGoing(response: Any?) {
        if (response == null) {
            btn_going.stopProgress()
            btn_refresh.performClick()
        }
    }

    private fun onGoingReport() {
        MaterialAlertDialogBuilder(this)
                .setTitle("Konfirmasi Penanganan Laporan")
                .setMessage("Apakah Anda yakin ingin membantu menangani laporan tersebut?")
                .setNegativeButton("Batal") { dialog, _ ->
                    dialog.dismiss()
                    btn_going.stopProgress()
                }
                .setPositiveButton("Yakin") { dialog, _ ->
                    dialog.dismiss()
                    btn_going.stopProgress()
                    when {
                        isKrama && isEmergency -> {
                            val requestParams = HashMap<String, String>()
                            requestParams["id_pelaporan_darurat"] = "${viewModel.report.value!!.id}"
                            requestParams["id_pecalang"] = "${me.pecalang!!.id}"

                            goEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessGoing,
                                    this::onRequestError)
                        }
                        isKrama && !isEmergency -> {
                            val requestParams = HashMap<String, String>()
                            requestParams["id_pelaporan"] = "${viewModel.report.value!!.id}"
                            requestParams["id_pecalang"] = "${me.pecalang!!.id}"

                            goNotEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessGoing,
                                    this::onRequestError)
                        }
                        !isKrama && isEmergency -> {
                            val requestParams = HashMap<String, String>()
                            requestParams["id_pelaporan_darurat_tamu"] = "${viewModel.guestReport.value!!.id}"
                            requestParams["id_pecalang"] = "${me.pecalang!!.id}"

                            goTamuEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessGoing,
                                    this::onRequestError)
                        }
                        else -> {
                            val requestParams = HashMap<String, String>()
                            requestParams["id_pelaporan_tamu"] = "${viewModel.guestReport.value!!.id}"
                            requestParams["id_pecalang"] = "${me.pecalang!!.id}"

                            goTamuNotEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessGoing,
                                    this::onRequestError)
                        }
                    }
                }.show()
    }

    private fun onSuccessGuestReport(response: Any?) {
        if (response != null) {
            viewModel.report.value = null
            viewModel.guestReport.value = response as PelaporanTamu
        }
    }

    private fun onSuccessReport(response: Any?) {
        if (response != null) {
            viewModel.guestReport.value = null
            viewModel.report.value = response as Pelaporan
        }
    }

    private fun onRequestError() {
        btn_going.stopProgress()
    }

    private fun onRequestData() {
        viewModel.report.value = null
        viewModel.guestReport.value = null

        content_container.visibility = View.GONE
        shimmer_container.visibility = View.VISIBLE
        shimmer_container.startShimmer()

        when {
            isKrama && isEmergency -> {
                val requestParams = HashMap<String, String>()
                requestParams["id_pelaporan_darurat"] = "$id"
                findEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessReport,
                        this::onRequestError, showMessage = false)
            }
            isKrama && !isEmergency -> {
                val requestParams = HashMap<String, String>()
                requestParams["id_pelaporan"] = "$id"
                findNotEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessReport,
                        this::onRequestError, showMessage = false)
            }
            !isKrama && isEmergency -> {
                val requestParams = HashMap<String, String>()
                requestParams["id_pelaporan_darurat_tamu"] = "$id"
                findTamuEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessGuestReport,
                        this::onRequestError, showMessage = false)
            }
            else -> {
                val requestParams = HashMap<String, String>()
                requestParams["id_pelaporan_tamu"] = "$id"
                findTamuNotEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessGuestReport,
                        this::onRequestError, showMessage = false)
            }
        }
    }

    private fun onToReportLocation() {
        if (this::map.isInitialized) {
            when {
                viewModel.report.value != null -> map.animateCamera(CameraUpdateFactory
                        .newLatLngZoom(LatLng(viewModel.report.value!!.latitude,
                                viewModel.report.value!!.longitude), 18.0f))
                else -> map.animateCamera(CameraUpdateFactory
                        .newLatLngZoom(LatLng(viewModel.guestReport.value!!.latitude,
                                viewModel.guestReport.value!!.longitude), 18.0f))
            }
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_report_detail)
        map_container.layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT,
                (0.75 * getViewport(this).heightPixels).toInt())

        viewModel = ViewModelProvider(this).get(ReportDetailViewModel::class.java)
        me = intent.getParcelableExtra<Me>("ME") as Me
        id = intent.getLongExtra("REPORT_ID", id)
        isEmergency = intent.getBooleanExtra("REPORT_EMERGENCY_STATUS", isEmergency)
        isKrama = intent.getBooleanExtra("IS_REPORTER_KRAMA", isKrama)

        view_pager.adapter = ReportDetailViewPagerAdapter(supportFragmentManager)
        view_pager.offscreenPageLimit = 2
        tabs.setupWithViewPager(view_pager)

        onRequestData()

        viewModel.report.observe(this, {
            if (it != null) {
                shimmer_container.stopShimmer()
                shimmer_container.visibility = View.GONE
                content_container.visibility = View.VISIBLE
                Handler(Looper.getMainLooper()).postDelayed({
                    view_pager.requestLayout()
                }, 300)

                emergency_status.apply {
                    setChipBackgroundColorResource(if (it.jenis_pelaporan.emergency_status)
                        R.color.red_700 else R.color.blue)
                    text = if (it.jenis_pelaporan.emergency_status) "Darurat" else "Keluhan"
                }
                status_container.setBackgroundResource(Constants.REPORT_STATUS_COLORS[it.status + 1])
                report_status.text = Constants.REPORT_STATUS_TITLES[it.status + 1]
                report_status_description.text = Constants.REPORT_STATUS_DESCRIPTIONS[it.status + 1]

                if (me.pecalang != null && me.pecalang!!.active_status &&
                        it.desa_adat.id == me.masyarakat.banjar.desa_adat.id) {
                    action_container.visibility = View.VISIBLE

                    if (!it.pecalang_reports.any { d -> d.pecalang.id == me.pecalang!!.id } && it.status == 0) {
                        btn_invalid.visibility = View.GONE
                        btn_valid.visibility = View.GONE
                        btn_siren.visibility = View.GONE
                    } else {
                        val reportStatus = it.pecalang_reports.find { d ->
                            d.pecalang.id == me.pecalang!!.id }

                        if (reportStatus != null && reportStatus.status == 0) {
                            btn_going.visibility = View.GONE
                            btn_siren.visibility = View.GONE
                            btn_invalid.visibility = View.VISIBLE
                            btn_valid.visibility = View.VISIBLE
                        } else if (it.status == 1 && me.pecalang!!.sirine_authority) {
                            btn_going.visibility = View.GONE
                            btn_invalid.visibility = View.GONE
                            btn_valid.visibility = View.GONE
                            btn_siren.visibility = View.VISIBLE
                        } else {
                            action_container.visibility = View.GONE
                        }
                    }
                }

                val mapFragment: SupportMapFragment = supportFragmentManager.findFragmentById(R.id.map_fragment)
                        as SupportMapFragment
                mapFragment.getMapAsync { gMap ->
                    map = gMap
                    map.addMarker(MarkerOptions().position(LatLng(it.latitude, it.longitude)))
                    onToReportLocation()
                }
            }
        })

        viewModel.guestReport.observe(this, {
            if (it != null) {
                shimmer_container.stopShimmer()
                shimmer_container.visibility = View.GONE
                content_container.visibility = View.VISIBLE
                Handler(Looper.getMainLooper()).postDelayed({
                    view_pager.requestLayout()
                }, 300)

                emergency_status.apply {
                    setChipBackgroundColorResource(if (it.jenis_pelaporan.emergency_status)
                        R.color.red_700 else R.color.blue)
                    text = if (it.jenis_pelaporan.emergency_status) "Darurat" else "Keluhan"
                }
                status_container.setBackgroundResource(Constants.REPORT_STATUS_COLORS[it.status + 1])
                report_status.text = Constants.REPORT_STATUS_TITLES[it.status + 1]
                report_status_description.text = Constants.REPORT_STATUS_DESCRIPTIONS[it.status + 1]

                if (me.pecalang != null && me.pecalang!!.active_status &&
                        it.desa_adat.id == me.masyarakat.banjar.desa_adat.id) {
                    action_container.visibility = View.VISIBLE

                    if (!it.pecalang_reports.any { d -> d.pecalang.id == me.pecalang!!.id } && it.status == 0) {
                        btn_invalid.visibility = View.GONE
                        btn_valid.visibility = View.GONE
                        btn_siren.visibility = View.GONE
                    } else {
                        val reportStatus = it.pecalang_reports.find { d ->
                            d.pecalang.id == me.pecalang!!.id }

                        if (reportStatus != null && reportStatus.status == 0) {
                            btn_going.visibility = View.GONE
                            btn_siren.visibility = View.GONE
                            btn_invalid.visibility = View.VISIBLE
                            btn_valid.visibility = View.VISIBLE
                        } else if (it.status == 1 && me.pecalang!!.sirine_authority) {
                            btn_going.visibility = View.GONE
                            btn_invalid.visibility = View.GONE
                            btn_valid.visibility = View.GONE
                            btn_siren.visibility = View.VISIBLE
                        } else {
                            action_container.visibility = View.GONE
                        }
                    }
                }

                val mapFragment: SupportMapFragment = supportFragmentManager.findFragmentById(R.id.map_fragment)
                        as SupportMapFragment
                mapFragment.getMapAsync { gMap ->
                    map = gMap
                    map.addMarker(MarkerOptions().position(LatLng(it.latitude, it.longitude)))
                    onToReportLocation()
                }
            }
        })

        btn_back.setOnClickListener { finish() }
        btn_refresh.setOnClickListener { onRequestData() }
        btn_to_location.setOnClickListener { onToReportLocation() }
        btn_going.setOnClickListener { onGoingReport() }
        btn_invalid.setOnClickListener {
            ReportBottomSheetDialog(me, false) { onRequestData() }
                    .show(supportFragmentManager, "REPORT_BOTTOM_SHEET_DIALOG")
        }
        btn_valid.setOnClickListener {
            ReportBottomSheetDialog(me, true) { onRequestData() }
                    .show(supportFragmentManager, "REPORT_BOTTOM_SHEET_DIALOG")
        }
        btn_siren.setOnClickListener {
            val intent = Intent(this, SirenActivity::class.java)
            intent.putExtra("DESA_ID", me.masyarakat.banjar.desa_adat.id)
            intent.putExtra("REPORT_TYPE_ID", if (viewModel.report.value != null)
                viewModel.report.value!!.jenis_pelaporan.id else viewModel.guestReport.value!!.jenis_pelaporan.id)
            startActivity(intent)
        }
    }
}