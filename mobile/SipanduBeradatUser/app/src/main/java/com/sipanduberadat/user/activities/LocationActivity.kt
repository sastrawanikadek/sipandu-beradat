package com.sipanduberadat.user.activities

import android.annotation.SuppressLint
import android.app.Activity
import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.LatLng
import com.google.android.gms.maps.model.Marker
import com.google.android.gms.maps.model.MarkerOptions
import com.sipanduberadat.user.R
import com.sipanduberadat.user.models.Me
import kotlinx.android.synthetic.main.activity_location.*

class LocationActivity : AppCompatActivity() {
    private lateinit var me: Me
    private lateinit var map: GoogleMap
    private lateinit var marker: Marker

    private fun onBack() {
        val intent = Intent()
        intent.putExtra("ME", me)
        setResult(Activity.RESULT_OK, intent)
        finish()
    }

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

        map.animateCamera(
            CameraUpdateFactory.newLatLngZoom(
                LatLng(me.masyarakat.home_latitude!!, me.masyarakat.home_longitude!!),
                18.0f))

        if (this::marker.isInitialized) {
            marker.remove()
        }

        marker = map.addMarker(MarkerOptions().position(LatLng(me.masyarakat.home_latitude!!,
            me.masyarakat.home_longitude!!)))
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_location)

        me = intent.getParcelableExtra("ME")!!
        negara.text = me.masyarakat.banjar.desa_adat.kecamatan.kabupaten.provinsi.negara.name
        provinsi.text = me.masyarakat.banjar.desa_adat.kecamatan.kabupaten.provinsi.name
        kabupaten.text = me.masyarakat.banjar.desa_adat.kecamatan.kabupaten.name
        kecamatan.text = me.masyarakat.banjar.desa_adat.kecamatan.name
        desa_adat.text = me.masyarakat.banjar.desa_adat.name
        banjar.text = me.masyarakat.banjar.name

        val mapFragment: SupportMapFragment = supportFragmentManager.findFragmentById(R.id.map_fragment)
                as SupportMapFragment
        mapFragment.getMapAsync {
            map = it
            initMap()
        }

        btn_back.setOnClickListener { onBack() }
        btn_edit.setOnClickListener {
            val intent = Intent(this, UpdateLocationActivity::class.java)
            intent.putExtra("ME", me)
            startActivityForResult(intent, 1)
        }
    }

    override fun onBackPressed() {
        onBack()
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)

        if (resultCode == Activity.RESULT_OK) {
            if (data != null) {
                if (requestCode == 1) {
                    me = data.getParcelableExtra("ME")!!
                    negara.text = me.masyarakat.banjar.desa_adat.kecamatan.kabupaten.provinsi.negara.name
                    provinsi.text = me.masyarakat.banjar.desa_adat.kecamatan.kabupaten.provinsi.name
                    kabupaten.text = me.masyarakat.banjar.desa_adat.kecamatan.kabupaten.name
                    kecamatan.text = me.masyarakat.banjar.desa_adat.kecamatan.name
                    desa_adat.text = me.masyarakat.banjar.desa_adat.name
                    banjar.text = me.masyarakat.banjar.name
                    initMap()
                }
            }
        }
    }
}