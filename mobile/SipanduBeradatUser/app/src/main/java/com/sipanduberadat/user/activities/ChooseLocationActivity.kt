package com.sipanduberadat.user.activities

import android.annotation.SuppressLint
import android.app.Activity
import android.content.Intent
import android.location.Location
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.LatLng
import com.google.android.gms.maps.model.Marker
import com.google.android.gms.maps.model.MarkerOptions
import com.sipanduberadat.user.R
import kotlinx.android.synthetic.main.activity_choose_location.*

class ChooseLocationActivity : AppCompatActivity() {
    @SuppressLint("MissingPermission")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_choose_location)

        val location = intent.getParcelableExtra<Location>("LOCATION")!!

        val mapFragment: SupportMapFragment = supportFragmentManager.findFragmentById(R.id.map_fragment)
                as SupportMapFragment
        mapFragment.getMapAsync {
            val map = it
            map.isMyLocationEnabled = true
            map.uiSettings.isMyLocationButtonEnabled = true

            map.animateCamera(
                CameraUpdateFactory.newLatLngZoom(
                    LatLng(location.latitude, location.longitude),
                    18.0f))

            var marker: Marker = map.addMarker(
                MarkerOptions().position(
                    LatLng(location.latitude,
                location.longitude)
                ))

            map.setOnMapClickListener { latlng ->
                marker.remove()
                location.latitude = latlng.latitude
                location.longitude = latlng.longitude
                marker = map.addMarker(
                    MarkerOptions().position(
                        LatLng(location.latitude,
                    location.longitude)
                    ))
            }
        }

        btn_back.setOnClickListener { finish() }

        btn_save.setOnClickListener {
            val intent = Intent()
            intent.putExtra("LOCATION", location)
            setResult(Activity.RESULT_OK, intent)
            finish()
        }
    }
}