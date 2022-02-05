package com.sipanduberadat.user.activities

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.View
import androidx.recyclerview.widget.LinearLayoutManager
import com.sipanduberadat.user.R
import com.sipanduberadat.user.adapters.SirenAdapter
import com.sipanduberadat.user.dialogs.SirenBottomSheetDialog
import com.sipanduberadat.user.models.Sirine
import com.sipanduberadat.user.services.apis.findAllSirineDesaAPI
import kotlinx.android.synthetic.main.activity_siren.*

class SirenActivity : AppCompatActivity() {
    private var reportTypeID: Long = 0

    private fun onRingSirine(siren: Sirine) {
        if (reportTypeID > 0) {
            SirenBottomSheetDialog(siren, reportTypeID) {}.show(supportFragmentManager,
                    "SIRINE_BOTTOM_SHEET_DIALOG")
        }
    }

    @Suppress("UNCHECKED_CAST")
    private fun onSuccessSirine(response: Any?) {
        if (response != null) {
            val sirens = (response as Array<Sirine>).toList()

            shimmer_container.stopShimmer()
            shimmer_container.visibility = View.GONE

            if (sirens.isNotEmpty()) {
                empty_container.visibility = View.GONE
                recycler_view.visibility = View.VISIBLE
                recycler_view.adapter = SirenAdapter(this, sirens, this::onRingSirine)
            } else {
                recycler_view.visibility = View.GONE
                empty_container.visibility = View.VISIBLE
            }
        }
    }

    private fun onRequestError() {}

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_siren)

        val desaAdatID = intent.getLongExtra("DESA_ID", -1)
        reportTypeID = intent.getLongExtra("REPORT_TYPE_ID", -1)

        recycler_view.layoutManager = LinearLayoutManager(this, LinearLayoutManager.VERTICAL,
            false)

        val requestParams = HashMap<String, String>()
        requestParams["id_desa"] = "$desaAdatID"
        findAllSirineDesaAPI(root, this, requestParams, HashMap(), this::onSuccessSirine,
            this::onRequestError, showMessage = false)

        btn_back.setOnClickListener { finish() }
    }
}