package com.sipanduberadat.user.activities

import android.annotation.SuppressLint
import android.graphics.Color
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.widget.LinearLayout
import com.bumptech.glide.Glide
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.LatLng
import com.google.android.gms.maps.model.PolylineOptions
import com.sipanduberadat.user.R
import com.sipanduberadat.user.models.PenutupanJalan
import com.sipanduberadat.user.utils.getDateTime
import com.sipanduberadat.user.utils.getTimeCountdown
import com.sipanduberadat.user.utils.getViewport
import com.sipanduberadat.user.utils.requestLocation
import kotlinx.android.synthetic.main.activity_blocked_road_detail.*
import java.util.*

class BlockedRoadDetailActivity : AppCompatActivity() {
    private lateinit var blockedRoad: PenutupanJalan
    private lateinit var map: GoogleMap
    private val statusBackgroundColors = listOf(R.color.blue, R.color.red_700, R.color.green)
    private val statusTitles = listOf("Belum Diberlakukan", "Sedang Diberlakukan", "Selesai")
    private val statusDescriptions = listOf("Penutupan jalan ini akan mulai diberlakukan dalam",
        "Penutupan jalan akan berakhir dalam", "Penutupan jalan telah selesai dilaksanakan pada")

    private fun onInitStatus() {
        val now = Calendar.getInstance().timeInMillis
        val status: Int = when {
            now < blockedRoad.start_time.time -> -1
            now >= blockedRoad.start_time.time && now <= blockedRoad.end_time.time -> 0
            else -> 1
        }
        status_container.setBackgroundResource(statusBackgroundColors[status + 1])
        blocked_road_status.text = statusTitles[status + 1]
        blocked_road_status_description.text = statusDescriptions[status + 1]
        blocked_road_timer.text = when (status) {
            -1 -> getTimeCountdown(now, blockedRoad.start_time.time)
            0 -> getTimeCountdown(now, blockedRoad.end_time.time)
            else -> "${getDateTime(blockedRoad.start_time, withSecond = false)} - " +
                    getDateTime(blockedRoad.end_time, withSecond = false)
        }
    }

    private fun onToLocation() {
        if (this::map.isInitialized) {
            requestLocation(this) {
                map.animateCamera(CameraUpdateFactory.newLatLngZoom(LatLng(it.latitude, it.longitude), 18.0f))
            }
        }
    }

    @SuppressLint("MissingPermission")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_blocked_road_detail)
        map_container.layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT,
                (0.75 * getViewport(this).heightPixels).toInt())
        blocked_road_legend_color.setBackgroundColor(Color.RED)
        allowed_road_legend_color.setBackgroundColor(Color.BLUE)

        blockedRoad = intent.getParcelableExtra("BLOCKED_ROAD")!!

        val handler = Handler(Looper.getMainLooper())
        val runnable = object: Runnable {
            override fun run() {
                onInitStatus()
                handler.postDelayed(this, 1000)
            }
        }
        val locationText = "Desa Adat ${blockedRoad.pecalang.masyarakat.banjar.desa_adat.name}"
        val mapFragment: SupportMapFragment = supportFragmentManager.findFragmentById(R.id.map_fragment)
                as SupportMapFragment

        title_text.text = blockedRoad.title
        Glide.with(this).load(blockedRoad.cover).centerCrop().into(photo)
        Glide.with(this).load(blockedRoad.pecalang.masyarakat.avatar).centerCrop().into(avatar)
        name.text = blockedRoad.pecalang.masyarakat.name
        location.text = locationText

        onInitStatus()
        handler.postDelayed(runnable, 1000)

        mapFragment.getMapAsync { gMap ->
            map = gMap
            map.isMyLocationEnabled = true
            map.uiSettings.isMyLocationButtonEnabled = false
            var isMove = false

            blockedRoad.points.map { route ->
                val polylineOptions = PolylineOptions().width(13f)
                    .color(if (route.type == 0) Color.RED else Color.BLUE)
                polylineOptions.addAll(route.coordinates.map {
                    if (!isMove) {
                        map.animateCamera(CameraUpdateFactory
                            .newLatLngZoom(LatLng(it.latitude, it.longitude), 18.0f))
                        isMove = true
                    }

                    LatLng(it.latitude, it.longitude)
                })
                map.addPolyline(polylineOptions)
            }
        }

        btn_back.setOnClickListener { finish() }
        btn_to_location.setOnClickListener { onToLocation() }
    }
}