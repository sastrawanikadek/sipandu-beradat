package com.sipanduberadat.guest.activities

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import android.view.inputmethod.EditorInfo
import androidx.recyclerview.widget.LinearLayoutManager
import com.bumptech.glide.Glide
import com.google.android.material.snackbar.Snackbar
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.adapters.HistoryAdapter
import com.sipanduberadat.guest.models.*
import com.sipanduberadat.guest.services.apis.*
import com.sipanduberadat.guest.utils.getDate
import com.sipanduberadat.guest.utils.snackbarSuccess
import kotlinx.android.synthetic.main.activity_profile_krama.*
import kotlinx.android.synthetic.main.layout_component_progress_button.view.*

class ProfileKramaActivity : AppCompatActivity() {
    private lateinit var kerabat: KerabatTamu
    private val reportHistories: MutableList<PelaporanTamu> = mutableListOf()

    private fun onSuccessActionKerabat(response: Any?) {
        if (response == null) {
            empty_container.visibility = View.GONE
            content_container.visibility = View.GONE
            shimmer_container.visibility = View.VISIBLE
            shimmer_container.startShimmer()

            val requestParams = HashMap<String, String>()
            requestParams["username"] = kerabat.tamu.username!!

            findKerabatTamuAPI(root, this, requestParams, HashMap(), this::onSuccessKerabat,
                    this::onRequestKerabatError, showMessage = false)
        }
    }

    private fun onSuccessRemoveKerabat(response: Any?) {
        if (response == null) {
            btn_action.stopProgress()
            snackbarSuccess(root, if (kerabat.status == 0) "The request has been cancelled" else
                "The user has been removed from family", Snackbar.LENGTH_LONG).show()

            empty_container.visibility = View.GONE
            content_container.visibility = View.GONE
            shimmer_container.visibility = View.VISIBLE
            shimmer_container.startShimmer()

            val requestParams = HashMap<String, String>()
            requestParams["username"] = kerabat.tamu.username!!

            findKerabatTamuAPI(root, this, requestParams, HashMap(), this::onSuccessKerabat,
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
            requestParams["username"] = kerabat.tamu.username!!

            findKerabatTamuAPI(root, this, requestParams, HashMap(), this::onSuccessKerabat,
                    this::onRequestKerabatError, showMessage = false)
        }
    }

    private fun onRequestError() { btn_action.stopProgress() }

    private fun onSuccessKerabat(response: Any?) {
        if (response != null) {
            kerabat = response as KerabatTamu
            val usernameText = "(${kerabat.tamu.username})"
            val locationText = "${kerabat.tamu.negara.name} - ${kerabat.tamu.akomodasi.name}"

            Glide.with(this).load(kerabat.tamu.avatar).centerCrop().into(avatar)
            name.text = kerabat.tamu.name
            username.text = usernameText
            gender.text = if (kerabat.tamu.gender == "l") "Male" else "Female"
            date_of_birth.text = getDate(kerabat.tamu.date_of_birth)
            location.text = locationText
            phone.text = kerabat.tamu.phone

            if (kerabat.initiator_status) {
                btn_action.text = when (kerabat.status) {
                    0 -> "Cancel Request"
                    1 -> "Remove Family"
                    else -> "Add Family"
                }
                btn_action.setOnClickListener {
                    when (kerabat.status) {
                        -1 -> {
                            val requestParams = HashMap<String, String>()
                            requestParams["id_kerabat"] = "${kerabat.tamu.id}"
                            addKerabatTamuAPI(root, this, requestParams, HashMap(), this::onSuccessAddKerabat,
                                    this::onRequestError)
                        }
                        else -> {
                            val requestParams = HashMap<String, String>()
                            requestParams["id"] = "${kerabat.id}"
                            removeKerabatTamuAPI(root, this, requestParams, HashMap(), this::onSuccessRemoveKerabat,
                                    this::onRequestError, showMessage = false)
                        }
                    }
                }
            } else {
                when (kerabat.status) {
                    -1 -> {
                        action_container.visibility = View.GONE
                        btn_action.visibility = View.VISIBLE
                        btn_action.text = "Add Family"
                        btn_action.setOnClickListener {
                            val requestParams = HashMap<String, String>()
                            requestParams["id_kerabat"] = "${kerabat.tamu.id}"
                            addKerabatTamuAPI(root, this, requestParams, HashMap(), this::onSuccessAddKerabat,
                                    this::onRequestError)
                        }
                    }
                    0 -> {
                        btn_action.visibility = View.GONE
                        action_container.visibility = View.VISIBLE
                        btn_decline.setOnClickListener {
                            val requestParams = HashMap<String, String>()
                            requestParams["id"] = "${kerabat.id}"
                            removeKerabatTamuAPI(root, this, requestParams, HashMap(), this::onSuccessActionKerabat,
                                    this::onRequestError)
                        }
                        btn_accept.setOnClickListener {
                            val requestParams = HashMap<String, String>()
                            requestParams["id"] = "${kerabat.id}"
                            acceptKerabatTamuAPI(root, this, requestParams, HashMap(), this::onSuccessActionKerabat,
                                    this::onRequestError)
                        }
                    }
                    else -> {
                        action_container.visibility = View.GONE
                        btn_action.visibility = View.VISIBLE
                        btn_action.text = "Remove Family"
                        btn_action.setOnClickListener {
                            val requestParams = HashMap<String, String>()
                            requestParams["id"] = "${kerabat.id}"
                            removeKerabatTamuAPI(root, this, requestParams, HashMap(), this::onSuccessRemoveKerabat,
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

        recycler_view.apply {
            layoutManager = LinearLayoutManager(this@ProfileKramaActivity,
                LinearLayoutManager.VERTICAL, false)
            adapter = HistoryAdapter(this@ProfileKramaActivity, reportHistories)
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
                findKerabatTamuAPI(root, this, requestParams, HashMap(), this::onSuccessKerabat,
                    this::onRequestKerabatError, showMessage = false)
                return@setOnEditorActionListener true
            }
            false
        }

        val requestParams = HashMap<String, String>()

        if (intent.hasExtra("USERNAME")) {
            requestParams["username"] = intent.getStringExtra("USERNAME")!!
        }

        if (intent.hasExtra("GUEST_ID")) {
            requestParams["id"] = intent.getStringExtra("GUEST_ID")!!
        }

        findKerabatTamuAPI(root, this, requestParams, HashMap(), this::onSuccessKerabat,
            this::onRequestKerabatError, showMessage = false)
    }
}