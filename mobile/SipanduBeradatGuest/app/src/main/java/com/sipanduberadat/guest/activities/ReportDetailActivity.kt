package com.sipanduberadat.guest.activities

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
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.adapters.ReportDetailViewPagerAdapter
import com.sipanduberadat.guest.models.Pelaporan
import com.sipanduberadat.guest.models.PelaporanTamu
import com.sipanduberadat.guest.services.apis.findEmergencyReportAPI
import com.sipanduberadat.guest.services.apis.findNotEmergencyReportAPI
import com.sipanduberadat.guest.services.apis.findTamuEmergencyReportAPI
import com.sipanduberadat.guest.services.apis.findTamuNotEmergencyReportAPI
import com.sipanduberadat.guest.utils.Constants
import com.sipanduberadat.guest.utils.getViewport
import com.sipanduberadat.guest.viewModels.ReportDetailViewModel
import kotlinx.android.synthetic.main.activity_report_detail.*

class ReportDetailActivity : AppCompatActivity() {
    private lateinit var viewModel: ReportDetailViewModel
    private lateinit var map: GoogleMap
    private var id: Long = -1
    private var isEmergency: Boolean = false
    private var isKrama: Boolean = true

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

    private fun onRequestError() {}

    private fun onRequestData() {
        viewModel.report.value = null
        viewModel.guestReport.value = null

        content_container.visibility = View.GONE
        shimmer_container.visibility = View.VISIBLE
        shimmer_container.startShimmer()

        if (isEmergency && isKrama) {
            val requestParams = HashMap<String, String>()
            requestParams["id_pelaporan_darurat"] = "$id"
            findEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessReport,
                    this::onRequestError, showMessage = false)
        } else if (isEmergency && !isKrama) {
            val requestParams = HashMap<String, String>()
            requestParams["id_pelaporan_darurat_tamu"] = "$id"
            findTamuEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessGuestReport,
                    this::onRequestError, showMessage = false)
        } else if (!isEmergency && isKrama) {
            val requestParams = HashMap<String, String>()
            requestParams["id_pelaporan"] = "$id"
            findNotEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessReport,
                    this::onRequestError, showMessage = false)
        } else {
            val requestParams = HashMap<String, String>()
            requestParams["id_pelaporan_tamu"] = "$id"
            findTamuNotEmergencyReportAPI(root, this, requestParams, HashMap(), this::onSuccessGuestReport,
                    this::onRequestError, showMessage = false)
        }
    }

    private fun onToReportLocation() {
        if (this::map.isInitialized) {
            when {
                viewModel.report.value != null -> map.moveCamera(CameraUpdateFactory
                        .newLatLngZoom(LatLng(viewModel.report.value!!.latitude,
                        viewModel.report.value!!.longitude), 18.0f))
                else -> map.moveCamera(CameraUpdateFactory
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
                    text = if (it.jenis_pelaporan.emergency_status) "Emergency" else "Complaint"
                }
                status_container.setBackgroundResource(Constants.REPORT_STATUS_COLORS[it.status + 1])
                report_status.text = Constants.REPORT_STATUS_TITLES[it.status + 1]
                report_status_description.text = Constants.REPORT_STATUS_DESCRIPTIONS[it.status + 1]

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
                    text = if (it.jenis_pelaporan.emergency_status) "Emergency" else "Complaint"
                }
                status_container.setBackgroundResource(Constants.REPORT_STATUS_COLORS[it.status + 1])
                report_status.text = Constants.REPORT_STATUS_TITLES[it.status + 1]
                report_status_description.text = Constants.REPORT_STATUS_DESCRIPTIONS[it.status + 1]

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
    }
}