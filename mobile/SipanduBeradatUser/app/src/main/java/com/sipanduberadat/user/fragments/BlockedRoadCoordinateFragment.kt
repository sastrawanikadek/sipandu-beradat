package com.sipanduberadat.user.fragments

import android.annotation.SuppressLint
import android.app.Activity
import android.graphics.Color
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.core.content.ContextCompat
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.*
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.google.android.material.snackbar.Snackbar
import com.sipanduberadat.user.R
import com.sipanduberadat.user.services.FileDataPart
import com.sipanduberadat.user.services.apis.createPenutupanJalanAPI
import com.sipanduberadat.user.utils.requestLocation
import com.sipanduberadat.user.utils.snackbarWarning
import com.sipanduberadat.user.viewModels.BlockedRoadViewModel
import kotlinx.android.synthetic.main.layout_blocked_road_coordinate.view.*
import java.util.*
import kotlin.collections.HashMap

class BlockedRoadCoordinateFragment: Fragment() {
    private lateinit var map: GoogleMap
    private lateinit var viewModel: BlockedRoadViewModel

    private fun onSuccessSave(response: Any?) {
        if (response != null) {
            activity!!.setResult(Activity.RESULT_OK)
            activity!!.finish()
        }
    }

    private fun onRequestError() { view!!.btn_save.isEnabled = true }

    private fun onSave() {
        view!!.btn_save.isEnabled = false
        Toast.makeText(view!!.context, "Sedang menyimpan...", Toast.LENGTH_SHORT).show()

        val requestParams = HashMap<String, String>()
        requestParams["title"] = viewModel.title.value!!
        requestParams["start_time"] = viewModel.startTime.value!!
        requestParams["end_time"] = viewModel.endTime.value!!
        requestParams["blocked_roads"] = viewModel.blockedRoads.value!!.filter { it.isNotEmpty() }.toString()
        requestParams["allowed_roads"] = viewModel.allowedRoads.value!!.filter { it.isNotEmpty() }.toString()

        val fileRequestParams = HashMap<String, FileDataPart>()
        fileRequestParams["cover"] = FileDataPart(UUID.randomUUID().toString(),
                viewModel.cover.value!!, "image/jpeg")

        createPenutupanJalanAPI(view!!, view!!.context, requestParams, fileRequestParams,
                this::onSuccessSave, this::onRequestError)
    }

    private fun onAdd() {
        if (viewModel.mode.value!! == 0) viewModel.blockedRoads.value!!.add(mutableListOf()) else
            viewModel.allowedRoads.value!!.add(mutableListOf())
        Toast.makeText(view!!.context, "Silahkan pilih lokasi untuk rute yang baru",
                Toast.LENGTH_LONG).show()
    }

    private fun onUndo() {
        if (viewModel.mode.value!! == 0) {
             viewModel.blockedRoads.apply {
                 value = value!!.filter { route -> route.isNotEmpty() }.toMutableList().apply {
                     if (size > 0) last { route -> route.remove(route.last()) } }
             }
        } else {
            viewModel.allowedRoads.apply {
                value = value!!.filter { route -> route.isNotEmpty() }.toMutableList().apply {
                    if (size > 0) last { route -> route.remove(route.last()) } }
            }
        }
    }

    private fun onDecorateMap() {
        if (this::map.isInitialized) {
            map.clear()

            viewModel.blockedRoads.value!!.map { route ->
                val polylineOptions = PolylineOptions().color(Color.RED).width(13f)
                route.map { coordinate ->
                    map.addMarker(MarkerOptions().position(LatLng(coordinate[0], coordinate[1])))
                    polylineOptions.add(LatLng(coordinate[0], coordinate[1]))
                }
                map.addPolyline(polylineOptions)
            }

            viewModel.allowedRoads.value!!.map { route ->
                val polylineOptions = PolylineOptions().color(Color.BLUE).width(13f)
                route.map { coordinate ->
                    map.addMarker(MarkerOptions().position(LatLng(coordinate[0], coordinate[1]))
                            .icon(BitmapDescriptorFactory.defaultMarker(BitmapDescriptorFactory.HUE_BLUE)))
                    polylineOptions.add(LatLng(coordinate[0], coordinate[1]))
                }
                map.addPolyline(polylineOptions)
            }
        }
    }

    private fun onClickMapMarker(marker: Marker): Boolean {
        val latitude = marker.position.latitude
        val longitude = marker.position.longitude

        viewModel.blockedRoads.value!!.map { route ->
            val coordinate: List<Double>? = route.find { latLng ->
                latitude == latLng[0] && longitude == latLng[1] }

            if (coordinate != null) {
                route.remove(coordinate)
                onDecorateMap()
                return true
            }
        }

        viewModel.allowedRoads.value!!.map { route ->
            val coordinate: List<Double>? = route.find { latLng ->
                latitude == latLng[0] && longitude == latLng[1] }

            if (coordinate != null) {
                route.remove(coordinate)
                onDecorateMap()
                return true
            }
        }

        return true
    }

    private fun onClickMap(pos: LatLng) {
        if (viewModel.mode.value!! == 0) {
            val tempBlockedRoads = viewModel.blockedRoads.value!!
            val latLng: List<Double> = listOf(pos.latitude, pos.longitude)

            if (tempBlockedRoads.size == 0) tempBlockedRoads.add(mutableListOf())
            tempBlockedRoads.last { it.add(latLng) }
            viewModel.blockedRoads.value = tempBlockedRoads
        } else {
            val tempAllowedRoads = viewModel.allowedRoads.value!!
            val latLng: List<Double> = listOf(pos.latitude, pos.longitude)

            if (tempAllowedRoads.size == 0) tempAllowedRoads.add(mutableListOf())
            tempAllowedRoads.last { it.add(latLng) }
            viewModel.allowedRoads.value = tempAllowedRoads
        }
    }

    private fun onToLocation() {
        if (this::map.isInitialized) {
            requestLocation(view!!.context) {
                map.animateCamera(CameraUpdateFactory.newLatLngZoom(LatLng(it.latitude, it.longitude), 18.0f))
            }
        }
    }

    @SuppressLint("MissingPermission")
    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_blocked_road_coordinate, container,
                false)
        val mapFragment: SupportMapFragment = childFragmentManager.findFragmentById(R.id.map_fragment)
                as SupportMapFragment
        viewModel = ViewModelProvider(activity!!).get(BlockedRoadViewModel::class.java)

        mapFragment.getMapAsync { gMap ->
            map = gMap
            map.isMyLocationEnabled = true
            map.uiSettings.isMyLocationButtonEnabled = false

            map.setOnMapClickListener { onClickMap(it) }
            map.setOnMarkerClickListener { onClickMapMarker(it) }
        }

        viewModel.mode.observe(activity!!, {
            view.btn_switch_mode.text = if (it == 0) "Jalan Ditutup" else "Jalan Alternatif"
            view.btn_switch_mode.setBackgroundColor(ContextCompat.getColor(view.context, if (it == 0)
                R.color.red_700 else R.color.blue))
        })

        viewModel.blockedRoads.observe(activity!!, { onDecorateMap() })
        viewModel.allowedRoads.observe(activity!!, { onDecorateMap() })

        view.btn_back.setOnClickListener { viewModel.currentPage.apply { value = value!! - 1 } }
        view.btn_to_location.setOnClickListener { onToLocation() }
        view.btn_switch_mode.setOnClickListener { viewModel.mode.apply { value = 1 - value!! } }
        view.btn_undo.setOnClickListener { onUndo() }
        view.btn_add.setOnClickListener { onAdd() }
        view.btn_save.setOnClickListener {
            val isBlockedRoadEmpty: Boolean = viewModel.blockedRoads.value!!.none { it.isNotEmpty() }
            val isAllowedRoadEmpty: Boolean = viewModel.allowedRoads.value!!.none { it.isNotEmpty() }

            if (isBlockedRoadEmpty) {
                snackbarWarning(view, "Anda belum memilih rute penutupan jalan",
                        Snackbar.LENGTH_LONG).show()
                return@setOnClickListener
            }

            if (isAllowedRoadEmpty) {
                MaterialAlertDialogBuilder(view.context)
                    .setTitle("Konfirmasi")
                    .setMessage("Apakah Anda yakin ingin menyimpan tanpa memilih rute alternatif?")
                    .setNegativeButton("Batal") { dialog, _ -> dialog.dismiss() }
                    .setPositiveButton("Yakin") { dialog, _ ->
                        dialog.dismiss()
                        onSave()
                    }.show()
                return@setOnClickListener
            }

            onSave()
        }
        return view
    }
}