package com.sipanduberadat.user.fragments

import android.annotation.SuppressLint
import android.app.Activity
import android.content.Intent
import android.location.Location
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.widget.addTextChangedListener
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.LatLng
import com.google.android.gms.maps.model.Marker
import com.google.android.gms.maps.model.MarkerOptions
import com.sipanduberadat.user.R
import com.sipanduberadat.user.activities.ChooseLocationActivity
import com.sipanduberadat.user.adapters.BanjarArrayAdapter
import com.sipanduberadat.user.adapters.DesaAdatArrayAdapter
import com.sipanduberadat.user.adapters.KecamatanArrayAdapter
import com.sipanduberadat.user.utils.requestLocation
import com.sipanduberadat.user.viewModels.RegisterViewModel
import kotlinx.android.synthetic.main.layout_register_contact.view.*

class RegisterContactFragment: Fragment() {
    private lateinit var viewModel: RegisterViewModel
    private lateinit var map: GoogleMap
    private lateinit var location: Location
    private lateinit var marker: Marker

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
                        LatLng(location.latitude, location.longitude),
                        18.0f))

        if (this::marker.isInitialized) {
            marker.remove()
        }

        marker = map.addMarker(MarkerOptions().position(LatLng(location.latitude, location.longitude)))
    }

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_register_contact, container, false)
        viewModel = ViewModelProvider(activity!!).get(RegisterViewModel::class.java)

        view.email_edit_text.addTextChangedListener { viewModel.email.value = "$it" }
        view.phone_edit_text.addTextChangedListener { viewModel.phone.value = "$it" }

        val autoCompleteLayouts = arrayOf(view.kabupaten_input_layout,
                view.kecamatan_input_layout, view.desa_adat_input_layout,
                view.banjar_input_layout)
        val autoCompletes = arrayOf(view.kabupaten_auto_complete,
                view.kecamatan_auto_complete, view.desa_adat_auto_complete,
                view.banjar_auto_complete)

        for (i in autoCompletes.indices) {
            autoCompletes[i].setOnItemClickListener { adapterView, _, pos, _ ->
                val selectedId = adapterView.getItemIdAtPosition(pos)

                when (i) {
                    0 -> viewModel.kabupaten.value = selectedId
                    1 -> viewModel.kecamatan.value = selectedId
                    2 -> viewModel.desaAdat.value = selectedId
                    3 -> viewModel.banjar.value = selectedId
                }

                for (j in i + 1 until autoCompletes.size) {
                    autoCompleteLayouts[j].visibility = View.GONE
                    autoCompletes[j].setText("")

                    if (j == i + 1) {
                        when (j) {
                            1 -> autoCompletes[j].setAdapter(KecamatanArrayAdapter(view.context,
                                    viewModel.kecamatans.value!!.filter { it.kabupaten.id == selectedId }))
                            2 -> autoCompletes[j].setAdapter(DesaAdatArrayAdapter(view.context,
                                    viewModel.desaAdats.value!!.filter { it.kecamatan.id == selectedId }))
                            3 -> autoCompletes[j].setAdapter(BanjarArrayAdapter(view.context,
                                    viewModel.banjars.value!!.filter { it.desa_adat.id == selectedId }))
                        }
                        autoCompleteLayouts[j].visibility = View.VISIBLE
                    }
                }
            }
        }

        val mapFragment: SupportMapFragment = childFragmentManager.findFragmentById(R.id.map_fragment)
                as SupportMapFragment
        mapFragment.getMapAsync {
            map = it
            requestLocation(view.context) { loc ->
                location = loc
                viewModel.homeLocation.value = location
                view.map_overlay.setOnClickListener {
                    val intent = Intent(view.context, ChooseLocationActivity::class.java)
                    intent.putExtra("LOCATION", location)
                    startActivityForResult(intent, 1)
                }
                initMap()
            }
        }

        return view
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)

        if (resultCode == Activity.RESULT_OK) {
            if (data != null) {
                if (requestCode == 1) {
                    location = data.getParcelableExtra("LOCATION")!!
                    viewModel.homeLocation.value = location
                    initMap()
                }
            }
        }
    }
}