package com.sipanduberadat.guest.activities

import android.annotation.SuppressLint
import android.app.Activity
import android.content.Intent
import android.graphics.Bitmap
import android.location.Location
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import com.bumptech.glide.Glide
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.LatLng
import com.google.android.gms.maps.model.Marker
import com.google.android.gms.maps.model.MarkerOptions
import com.google.android.material.snackbar.Snackbar
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.adapters.JenisPelaporanArrayAdapter
import com.sipanduberadat.guest.models.JenisPelaporan
import com.sipanduberadat.guest.services.FileDataPart
import com.sipanduberadat.guest.services.apis.createNotEmergencyReportAPI
import com.sipanduberadat.guest.services.apis.findAllJenisPelaporanAPI
import com.sipanduberadat.guest.utils.choosePhoto
import com.sipanduberadat.guest.utils.requestLocation
import com.sipanduberadat.guest.utils.snackbarWarning
import kotlinx.android.synthetic.main.activity_add_report.*
import kotlinx.android.synthetic.main.activity_add_report.root
import java.io.ByteArrayOutputStream
import java.util.*
import kotlin.collections.HashMap

class AddReportActivity : AppCompatActivity() {
    private lateinit var map: GoogleMap
    private lateinit var location: Location
    private lateinit var marker: Marker
    private var reportType: Long  = -1
    private var photoByteArray: ByteArray? = null
    private var resultCode: Int = Activity.RESULT_CANCELED

    private fun onSuccessCreateNotEmergencyReport(response: Any?) {
        if (response != null) {
            btn_report.stopProgress()
            resultCode = Activity.RESULT_OK
            finish()
        }
    }

    private fun onReport() {
        val reportTitle = "${title_edit_text.text}"
        val reportDescription = "${description_edit_text.text}"

        if (reportType == (-1).toLong()) {
            btn_report.stopProgress()
            report_type_input_layout.helperText = "Complaint type cannot be empty"
            report_type_input_layout.requestFocus()
            return
        } else {
            report_type_input_layout.helperText = ""
        }

        if (reportTitle.isBlank()) {
            btn_report.stopProgress()
            title_input_layout.helperText = "Complaint title cannot be empty"
            title_input_layout.requestFocus()
            return
        } else {
            title_input_layout.helperText = ""
        }

        if (reportDescription.isBlank()) {
            btn_report.stopProgress()
            description_input_layout.helperText = "Complaint description cannot be empty"
            description_input_layout.requestFocus()
            return
        } else {
            description_input_layout.helperText = ""
        }

        if (photoByteArray == null) {
            btn_report.stopProgress()
            snackbarWarning(root, "Complaint photo cannot be empty",
                Snackbar.LENGTH_LONG).show()
            return
        }

        val requestParams = HashMap<String, String>()
        requestParams["id_jenis_pelaporan"] = "$reportType"
        requestParams["title"] = reportTitle
        requestParams["description"] = reportDescription
        requestParams["latitude"] = "${location.latitude}"
        requestParams["longitude"] = "${location.longitude}"

        val fileRequestParams = HashMap<String, FileDataPart>()
        fileRequestParams["photo"] = FileDataPart(UUID.randomUUID().toString(), photoByteArray!!,
            "image/jpeg")

        createNotEmergencyReportAPI(root, this, requestParams, fileRequestParams,
            this::onSuccessCreateNotEmergencyReport, this::onRequestError)
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessReportType(response: Any?) {
        if (response != null) {
            val notEmergencyReportType = (response as Array<JenisPelaporan>).toList().filter { !it.emergency_status }
            report_type_auto_complete.setAdapter(JenisPelaporanArrayAdapter(this, notEmergencyReportType))

            shimmer_container.stopShimmer()
            shimmer_container.visibility = View.GONE
            content_container.visibility = View.VISIBLE
        }
    }

    private fun onRequestError() { btn_report.stopProgress() }

    @SuppressLint("MissingPermission")
    private fun initMap() {
        map.uiSettings.apply {
            isZoomControlsEnabled = false
            isZoomGesturesEnabled = false
            isMyLocationButtonEnabled = false
            isScrollGesturesEnabled = false
            isScrollGesturesEnabledDuringRotateOrZoom = false
            isMapToolbarEnabled = false
        }

        map.moveCamera(
            CameraUpdateFactory.newLatLngZoom(
                LatLng(location.latitude, location.longitude),
                18.0f))

        if (this::marker.isInitialized) {
            marker.remove()
        }

        marker = map.addMarker(MarkerOptions().position(LatLng(location.latitude, location.longitude)))
    }

    private fun onBack() {
        setResult(resultCode)
        finish()
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_add_report)

        btn_back.setOnClickListener { onBack() }

        report_type_auto_complete.setAdapter(JenisPelaporanArrayAdapter(this, listOf()))
        report_type_auto_complete.setOnItemClickListener { _, _, _, id ->
            reportType = id
        }

        val mapFragment: SupportMapFragment = supportFragmentManager.findFragmentById(R.id.map_fragment)
                as SupportMapFragment
        mapFragment.getMapAsync {
            map = it
            requestLocation(this) { loc ->
                location = loc
                map_overlay.setOnClickListener {
                    val intent = Intent(this, ChooseLocationActivity::class.java)
                    intent.putExtra("LOCATION", location)
                    startActivityForResult(intent, 1)
                }
                initMap()
            }
        }

        photo.setOnClickListener {
            val intentChooser = choosePhoto()
            startActivityForResult(intentChooser, 2)
        }
        btn_report.setOnClickListener { onReport() }

        findAllJenisPelaporanAPI(root, this, HashMap(), HashMap(),
            this::onSuccessReportType, this::onRequestError, showMessage = false)
    }

    override fun onBackPressed() {
        onBack()
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)

        if (resultCode == Activity.RESULT_OK) {
            if (data != null) {
                if (requestCode == 1) {
                    location = data.getParcelableExtra("LOCATION")!!
                    initMap()
                } else if (requestCode == 2) {
                    if (data.data != null) {
                        val uri = data.data
                        val byteArray =
                            contentResolver.openInputStream(uri!!)?.buffered()?.use {
                                it.readBytes()
                            }
                        Glide.with(this).load(uri).centerCrop().into(photo)
                        photoByteArray = byteArray
                        return
                    }

                    val bitmap = data.extras!!.get("data") as Bitmap
                    val stream = ByteArrayOutputStream()
                    bitmap.compress(Bitmap.CompressFormat.JPEG, 100, stream)
                    photoByteArray = stream.toByteArray()
                    Glide.with(this).load(bitmap).centerCrop().into(photo)
                }
            }
        }
    }
}