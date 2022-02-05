package com.sipanduberadat.guest.viewHolders

import android.content.Intent
import android.view.View
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.google.android.flexbox.FlexboxLayoutManager
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.activities.ReportDetailActivity
import com.sipanduberadat.guest.models.JenisPelaporan
import com.sipanduberadat.guest.models.PelaporanTamu
import com.sipanduberadat.guest.services.apis.createEmergencyReportAPI
import com.sipanduberadat.guest.utils.requestLocation
import com.sipanduberadat.guest.utils.toPx
import com.sipanduberadat.guest.viewModels.MainViewModel
import kotlinx.android.synthetic.main.layout_item_emergency.view.*

class EmergencyViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    private lateinit var viewModel: MainViewModel

    private fun onSuccessCreateEmergencyReport(response: Any?) {
        if (response != null) {
            val report = response as PelaporanTamu
            val intent = Intent(view.context, ReportDetailActivity::class.java)
            intent.putExtra("REPORT_ID", report.id)
            intent.putExtra("REPORT_EMERGENCY_STATUS", true)
            intent.putExtra("IS_REPORTER_KRAMA", false)
            view.context.startActivity(intent)
            viewModel.reportHistories.value = null
        }
    }

    private fun onRequestError() {}

    fun onBindItem(emergencyReportType: JenisPelaporan, mainViewModel: MainViewModel, position: Int) {
        viewModel = mainViewModel

        Glide.with(view.context).load(emergencyReportType.icon).into(view.icon)
        view.name.text = emergencyReportType.name

        val lp = view.container.layoutParams
        if (lp is FlexboxLayoutManager.LayoutParams) {
            lp.apply {
                lp.flexBasisPercent = 0.315f
                setMargins(if (position % 3 == 1) (4).toPx() else 0, 0,
                    if (position % 3 == 1) (4).toPx() else 0, (8).toPx())
            }
        }

        view.container.setOnClickListener {
            if (!viewModel.me.value!!.valid_status) {
                MaterialAlertDialogBuilder(view.context)
                    .setTitle("Account Not Validated")
                    .setMessage("Please ask for validation in advance from the admin at the " +
                            "accommodation so that you can submit a report")
                    .setPositiveButton("Close") { dialog, _ ->
                        dialog.dismiss()
                    }
                    .show()
                return@setOnClickListener
            } else if (viewModel.me.value!!.block_status) {
                MaterialAlertDialogBuilder(view.context)
                    .setTitle("Account Blocked")
                    .setMessage("Your account has been blocked because of invalid report. " +
                            "Please ask your accommodation admin to unblock your account")
                    .setPositiveButton("Close") { dialog, _ ->
                        dialog.dismiss()
                    }
                    .show()
                return@setOnClickListener
            }

            MaterialAlertDialogBuilder(view.context)
                    .setTitle(emergencyReportType.name)
                    .setMessage(R.string.confirm_emergency_report)
                    .setNegativeButton(R.string.cancel) { dialog, _ ->
                        dialog.dismiss()
                    }
                    .setPositiveButton(R.string.next) { dialog, _ ->
                        requestLocation(view.context) {
                            val requestParams = HashMap<String, String>()
                            requestParams["id_jenis_pelaporan"] = "${emergencyReportType.id}"
                            requestParams["latitude"] = "${it.latitude}"
                            requestParams["longitude"] = "${it.longitude}"

                            createEmergencyReportAPI(view.rootView, view.context, requestParams, HashMap(),
                                    this::onSuccessCreateEmergencyReport, this::onRequestError)
                            dialog.dismiss()
                        }
                    }.show()
        }
    }
}