package com.sipanduberadat.user.activities

import android.annotation.SuppressLint
import android.app.Activity
import android.content.Intent
import android.location.Location
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.widget.AutoCompleteTextView
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.LatLng
import com.google.android.gms.maps.model.Marker
import com.google.android.gms.maps.model.MarkerOptions
import com.google.android.material.textfield.TextInputLayout
import com.sipanduberadat.user.R
import com.sipanduberadat.user.adapters.BanjarArrayAdapter
import com.sipanduberadat.user.adapters.DesaAdatArrayAdapter
import com.sipanduberadat.user.adapters.KabupatenArrayAdapter
import com.sipanduberadat.user.adapters.KecamatanArrayAdapter
import com.sipanduberadat.user.models.*
import com.sipanduberadat.user.services.apis.*
import kotlinx.android.synthetic.main.activity_update_location.*

class UpdateLocationActivity : AppCompatActivity() {
    private lateinit var autoCompleteLayouts: Array<TextInputLayout>
    private lateinit var autoCompletes: Array<AutoCompleteTextView>
    private lateinit var me: Me
    private lateinit var map: GoogleMap
    private lateinit var marker: Marker
    private var kabupatens: List<Kabupaten> = listOf()
    private var kecamatans: List<Kecamatan> = listOf()
    private var desaAdats: List<DesaAdat> = listOf()
    private var banjars: List<Banjar> = listOf()
    private var banjar: Long = -1
    private var resultCode: Int = Activity.RESULT_CANCELED

    private fun onSuccessChangeLocation(response: Any?) {
        if (response != null) {
            btn_save.stopProgress()
            me.masyarakat.banjar = (response as Krama).banjar
            resultCode = Activity.RESULT_OK
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessBanjar(response: Any?) {
        if (response != null) {
            banjars = (response as Array<Banjar>).toList()

            negara_edit_text.setText(me.masyarakat.banjar.desa_adat.kecamatan.kabupaten.provinsi.negara.name)
            provinsi_edit_text.setText(me.masyarakat.banjar.desa_adat.kecamatan.kabupaten.provinsi.name)
            kabupaten_auto_complete.setText(me.masyarakat.banjar.desa_adat.kecamatan.kabupaten.name)

            kecamatan_input_layout.visibility = View.VISIBLE
            kecamatan_auto_complete.setAdapter(KecamatanArrayAdapter(this,
                kecamatans.filter { it.kabupaten.id == me.masyarakat.banjar.desa_adat.kecamatan.kabupaten.id }))
            kecamatan_auto_complete.setText(me.masyarakat.banjar.desa_adat.kecamatan.name)

            desa_adat_input_layout.visibility = View.VISIBLE
            desa_adat_auto_complete.setAdapter(DesaAdatArrayAdapter(this,
                desaAdats.filter { it.kecamatan.id == me.masyarakat.banjar.desa_adat.kecamatan.id }))
            desa_adat_auto_complete.setText(me.masyarakat.banjar.desa_adat.name)

            banjar_input_layout.visibility = View.VISIBLE
            banjar_auto_complete.setAdapter(BanjarArrayAdapter(this,
                banjars.filter { it.desa_adat.id == me.masyarakat.banjar.desa_adat.id }))
            banjar_auto_complete.setText(me.masyarakat.banjar.name)

            shimmer_container.stopShimmer()
            shimmer_container.visibility = View.GONE
            content_container.visibility = View.VISIBLE
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessDesaAdat(response: Any?) {
        if (response != null) {
            desaAdats = (response as Array<DesaAdat>).toList()
            findAllBanjarAPI(root, this, HashMap(), HashMap(), this::onSuccessBanjar,
                this::onRequestError, showMessage = false)
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessKecamatan(response: Any?) {
        if (response != null) {
            kecamatans = (response as Array<Kecamatan>).toList()
            findAllDesaAdatAPI(root, this, HashMap(), HashMap(), this::onSuccessDesaAdat,
                this::onRequestError, showMessage = false)
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessKabupaten(response: Any?) {
        if (response != null) {
            kabupatens = (response as Array<Kabupaten>).toList()
            kabupaten_auto_complete.setAdapter(KabupatenArrayAdapter(this, kabupatens))
            findAllKecamatanAPI(root, this, HashMap(), HashMap(), this::onSuccessKecamatan,
                this::onRequestError, showMessage = false)
        }
    }

    private fun onRequestError() { btn_save.stopProgress() }

    private fun onBack() {
        val intent = Intent()
        intent.putExtra("ME", me)
        setResult(resultCode, intent)
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

        marker = map.addMarker(
            MarkerOptions().position(
                LatLng(me.masyarakat.home_latitude!!,
            me.masyarakat.home_longitude!!)
            ))
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_update_location)

        me = intent.getParcelableExtra("ME")!!

        autoCompleteLayouts = arrayOf(kabupaten_input_layout,
            kecamatan_input_layout, desa_adat_input_layout,
            banjar_input_layout)
        autoCompletes = arrayOf(kabupaten_auto_complete,
            kecamatan_auto_complete, desa_adat_auto_complete,
            banjar_auto_complete)

        for (i in autoCompletes.indices) {
            autoCompletes[i].setOnItemClickListener { adapterView, _, pos, _ ->
                val selectedId = adapterView.getItemIdAtPosition(pos)
                banjar = if (i == 3) selectedId else banjar

                for (j in i + 1 until autoCompletes.size) {
                    autoCompleteLayouts[j].visibility = View.GONE
                    autoCompletes[j].setText("")

                    if (j == i + 1) {
                        when (j) {
                            1 -> autoCompletes[j].setAdapter(
                                KecamatanArrayAdapter(this,
                                    kecamatans.filter { it.kabupaten.id == selectedId })
                            )
                            2 -> autoCompletes[j].setAdapter(
                                DesaAdatArrayAdapter(this,
                                    desaAdats.filter { it.kecamatan.id == selectedId })
                            )
                            3 -> autoCompletes[j].setAdapter(
                                BanjarArrayAdapter(this,
                                    banjars.filter { it.desa_adat.id == selectedId })
                            )
                        }
                        autoCompleteLayouts[j].visibility = View.VISIBLE
                    }
                }
            }
        }

        val mapFragment: SupportMapFragment = supportFragmentManager.findFragmentById(R.id.map_fragment)
                as SupportMapFragment
        mapFragment.getMapAsync {
            map = it
            map_overlay.setOnClickListener {
                val intent = Intent(this, ChooseLocationActivity::class.java)
                val location = Location("").apply {
                    latitude = me.masyarakat.home_latitude!!
                    longitude = me.masyarakat.home_longitude!!
                }
                intent.putExtra("LOCATION", location)
                startActivityForResult(intent, 1)
            }
            initMap()
        }

        findAllKabupatenAPI(root, this, HashMap(), HashMap(), this::onSuccessKabupaten,
            this::onRequestError, showMessage = false)

        btn_back.setOnClickListener { onBack() }
        btn_save.setOnClickListener {
            val requestParams = HashMap<String, String>()
            requestParams["id_banjar"] = "${if (banjar == (-1).toLong()) me.masyarakat.banjar.id else banjar}"
            requestParams["home_latitude"] = "${me.masyarakat.home_latitude}"
            requestParams["home_longitude"] = "${me.masyarakat.home_longitude}"
            changeLocationAPI(root, this, requestParams, HashMap(), this::onSuccessChangeLocation,
                this::onRequestError)
        }
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)

        if (resultCode == Activity.RESULT_OK) {
            if (data != null) {
                if (requestCode == 1) {
                    val location: Location = data.getParcelableExtra("LOCATION")!!
                    me.masyarakat.home_latitude = location.latitude
                    me.masyarakat.home_longitude = location.longitude
                    initMap()
                }
            }
        }
    }

    override fun onBackPressed() {
        onBack()
    }
}