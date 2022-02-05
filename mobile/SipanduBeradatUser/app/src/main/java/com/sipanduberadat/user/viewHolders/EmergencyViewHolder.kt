package com.sipanduberadat.user.viewHolders

import android.content.Intent
import android.view.View
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.google.android.flexbox.FlexboxLayoutManager
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.sipanduberadat.user.R
import com.sipanduberadat.user.activities.ReportDetailActivity
import com.sipanduberadat.user.models.JenisPelaporan
import com.sipanduberadat.user.models.Pelaporan
import com.sipanduberadat.user.services.apis.createEmergencyReportAPI
import com.sipanduberadat.user.utils.requestLocation
import com.sipanduberadat.user.utils.toPx
import com.sipanduberadat.user.viewModels.MainViewModel
import kotlinx.android.synthetic.main.layout_item_emergency.view.*

class EmergencyViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    private lateinit var viewModel: MainViewModel

    private fun onSuccessCreateEmergencyReport(response: Any?) {
        if (response != null) {
            val report = response as Pelaporan
            val intent = Intent(view.context, ReportDetailActivity::class.java)
            intent.putExtra("REPORT_ID", report.id)
            intent.putExtra("REPORT_EMERGENCY_STATUS", true)
            intent.putExtra("ME", viewModel.me.value!!)
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
            if (!viewModel.me.value!!.masyarakat.valid_status) {
                MaterialAlertDialogBuilder(view.context)
                        .setTitle("Akun belum tervalidasi")
                        .setMessage("Mohon minta validasi terlebih dahulu dari admin di desa adat " +
                                "agar dapat mengajukan pelaporan")
                        .setPositiveButton("Tutup") { dialog, _ ->
                            dialog.dismiss()
                        }
                        .show()
                return@setOnClickListener
            } else if (viewModel.me.value!!.masyarakat.block_status) {
                MaterialAlertDialogBuilder(view.context)
                        .setTitle("Akun Terblokir")
                        .setMessage("Akun Anda telah terblokir karena pelaporan tidak valid. " +
                                "Mohon minta admin di desa adat untuk membuka blokirnya " +
                                "agar dapat mengajukan pelaporan")
                        .setPositiveButton("Tutup") { dialog, _ ->
                            dialog.dismiss()
                        }
                        .show()
                return@setOnClickListener
            }

            MaterialAlertDialogBuilder(view.context)
                    .setTitle(emergencyReportType.name)
                    .setMessage(R.string.konfirmasi_pelaporan_darurat)
                    .setNegativeButton(R.string.batal) { dialog, _ ->
                        dialog.dismiss()
                    }
                    .setPositiveButton(R.string.lanjutkan) { dialog, _ ->
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