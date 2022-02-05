package com.sipanduberadat.user.activities

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.view.inputmethod.EditorInfo
import androidx.recyclerview.widget.LinearLayoutManager
import com.bumptech.glide.Glide
import com.google.android.material.snackbar.Snackbar
import com.sipanduberadat.user.R
import com.sipanduberadat.user.adapters.HistoryAdapter
import com.sipanduberadat.user.models.Kerabat
import com.sipanduberadat.user.models.Me
import com.sipanduberadat.user.models.Pelaporan
import com.sipanduberadat.user.services.apis.acceptKerabatAPI
import com.sipanduberadat.user.services.apis.addKerabatAPI
import com.sipanduberadat.user.services.apis.findKerabatAPI
import com.sipanduberadat.user.services.apis.removeKerabatAPI
import com.sipanduberadat.user.utils.getDate
import com.sipanduberadat.user.utils.snackbarSuccess
import kotlinx.android.synthetic.main.activity_profile_krama.*
import kotlinx.android.synthetic.main.layout_component_progress_button.view.*

class ProfileKramaActivity : AppCompatActivity() {
    private lateinit var kerabat: Kerabat
    private lateinit var me: Me
    private val reportHistories: MutableList<Pelaporan> = mutableListOf()

    private fun onSuccessActionKerabat(response: Any?) {
        if (response == null) {
            empty_container.visibility = View.GONE
            content_container.visibility = View.GONE
            shimmer_container.visibility = View.VISIBLE
            shimmer_container.startShimmer()

            val requestParams = HashMap<String, String>()
            requestParams["username"] = kerabat.masyarakat.username!!

            findKerabatAPI(root, this, requestParams, HashMap(), this::onSuccessKerabat,
                    this::onRequestKerabatError, showMessage = false)
        }
    }

    private fun onSuccessRemoveKerabat(response: Any?) {
        if (response == null) {
            btn_action.stopProgress()
            snackbarSuccess(root, if (kerabat.status == 0) "Permintaan kerabat telah dibatalkan" else
                "Kerabat telah dihapus", Snackbar.LENGTH_LONG).show()

            empty_container.visibility = View.GONE
            content_container.visibility = View.GONE
            shimmer_container.visibility = View.VISIBLE
            shimmer_container.startShimmer()

            val requestParams = HashMap<String, String>()
            requestParams["username"] = kerabat.masyarakat.username!!
            findKerabatAPI(root, this, requestParams, HashMap(), this::onSuccessKerabat,
                    this::onRequestKerabatError, showMessage = false)
        }
    }

    private fun onSuccessAddKerabat(response: Any?) {
        if (response != null) {
            btn_action.stopProgress()
            empty_container.visibility = View.GONE
            content_container.visibility = View.GONE
            shimmer_container.visibility = View.VISIBLE
            shimmer_container.startShimmer()

            val requestParams = HashMap<String, String>()
            requestParams["username"] = kerabat.masyarakat.username!!
            findKerabatAPI(root, this, requestParams, HashMap(), this::onSuccessKerabat,
                    this::onRequestKerabatError, showMessage = false)
        }
    }

    private fun onRequestError() { btn_action.stopProgress() }

    private fun onSuccessKerabat(response: Any?) {
        if (response != null) {
            reportHistories.clear()
            kerabat = response as Kerabat
            val usernameText = "(${kerabat.masyarakat.username})"
            val locationText = "Banjar ${kerabat.masyarakat.banjar.name}, " +
                    "Desa Adat ${kerabat.masyarakat.banjar.desa_adat.name}, " +
                    "Kabupaten ${kerabat.masyarakat.banjar.desa_adat.kecamatan.kabupaten.name}"

            Glide.with(this).load(kerabat.masyarakat.avatar).centerCrop().into(avatar)
            name.text = kerabat.masyarakat.name
            username.text = usernameText
            gender.text = if (kerabat.masyarakat.gender == "l") "Laki-laki" else "Perempuan"
            date_of_birth.text = getDate(kerabat.masyarakat.date_of_birth)
            location.text = locationText
            phone.text = kerabat.masyarakat.phone

            if (kerabat.initiator_status) {
                btn_action.text = when (kerabat.status) {
                    0 -> "Batalkan Permintaan"
                    1 -> "Hapus Kerabat"
                    else -> "Tambah Kerabat"
                }
                btn_action.setOnClickListener {
                    when (kerabat.status) {
                        -1 -> {
                            val requestParams = HashMap<String, String>()
                            requestParams["id_kerabat"] = "${kerabat.masyarakat.id}"
                            addKerabatAPI(root, this, requestParams, HashMap(), this::onSuccessAddKerabat,
                                    this::onRequestError)
                        }
                        else -> {
                            val requestParams = HashMap<String, String>()
                            requestParams["id"] = "${kerabat.id}"
                            removeKerabatAPI(root, this, requestParams, HashMap(), this::onSuccessRemoveKerabat,
                                    this::onRequestError, showMessage = false)
                        }
                    }
                }
            } else {
                when (kerabat.status) {
                    -1 -> {
                        action_container.visibility = View.GONE
                        btn_action.visibility = View.VISIBLE
                        btn_action.text = "Tambah Kerabat"
                        btn_action.setOnClickListener {
                            val requestParams = HashMap<String, String>()
                            requestParams["id_kerabat"] = "${kerabat.masyarakat.id}"
                            addKerabatAPI(root, this, requestParams, HashMap(), this::onSuccessAddKerabat,
                                    this::onRequestError)
                        }
                    }
                    0 -> {
                        btn_action.visibility = View.GONE
                        action_container.visibility = View.VISIBLE
                        btn_decline.setOnClickListener {
                            val requestParams = HashMap<String, String>()
                            requestParams["id"] = "${kerabat.id}"
                            removeKerabatAPI(root, this, requestParams, HashMap(), this::onSuccessActionKerabat,
                                    this::onRequestError)
                        }
                        btn_accept.setOnClickListener {
                            val requestParams = HashMap<String, String>()
                            requestParams["id"] = "${kerabat.id}"
                            acceptKerabatAPI(root, this, requestParams, HashMap(), this::onSuccessActionKerabat,
                                    this::onRequestError)
                        }
                    }
                    else -> {
                        action_container.visibility = View.GONE
                        btn_action.visibility = View.VISIBLE
                        btn_action.text = "Hapus Kerabat"
                        btn_action.setOnClickListener {
                            val requestParams = HashMap<String, String>()
                            requestParams["id"] = "${kerabat.id}"
                            removeKerabatAPI(root, this, requestParams, HashMap(), this::onSuccessRemoveKerabat,
                                    this::onRequestError, showMessage = false)
                        }
                    }
                }
            }

            reportHistories.addAll(kerabat.pelaporan.sortedByDescending { it.time.time })
            recycler_view.adapter!!.notifyDataSetChanged()

            shimmer_container.stopShimmer()
            shimmer_container.visibility = View.GONE
            empty_container.visibility = View.GONE
            content_container.visibility = View.VISIBLE
        }
    }

    private fun onRequestKerabatError() {
        shimmer_container.stopShimmer()
        shimmer_container.visibility = View.GONE
        content_container.visibility = View.GONE
        empty_container.visibility = View.VISIBLE
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_profile_krama)

        me = intent.getParcelableExtra<Me>("ME") as Me

        recycler_view.apply {
            layoutManager = LinearLayoutManager(this@ProfileKramaActivity,
                LinearLayoutManager.VERTICAL, false)
            adapter = HistoryAdapter(this@ProfileKramaActivity, reportHistories, me)
        }

        btn_back.setOnClickListener { finish() }
        username_edit_text.setOnEditorActionListener { _, i, _ ->
            if (i == EditorInfo.IME_ACTION_SEARCH) {
                empty_container.visibility = View.GONE
                content_container.visibility = View.GONE
                shimmer_container.visibility = View.VISIBLE
                shimmer_container.startShimmer()

                val requestParams = HashMap<String, String>()
                requestParams["username"] = "${username_edit_text.text}"
                findKerabatAPI(root, this, requestParams, HashMap(), this::onSuccessKerabat,
                    this::onRequestKerabatError, showMessage = false)
                return@setOnEditorActionListener true
            }
            false
        }

        val requestParams = HashMap<String, String>()

        if (intent.hasExtra("USERNAME")) {
            requestParams["username"] = intent.getStringExtra("USERNAME")!!
        }

        if (intent.hasExtra("KRAMA_ID")) {
            requestParams["id"] = intent.getStringExtra("KRAMA_ID")!!
        }

        findKerabatAPI(root, this, requestParams, HashMap(), this::onSuccessKerabat,
            this::onRequestKerabatError, showMessage = false)
    }
}