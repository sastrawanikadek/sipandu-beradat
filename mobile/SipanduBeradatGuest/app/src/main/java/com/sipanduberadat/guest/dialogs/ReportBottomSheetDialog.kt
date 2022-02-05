package com.sipanduberadat.guest.dialogs

import android.app.Activity
import android.content.DialogInterface
import android.content.Intent
import android.graphics.Bitmap
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.lifecycle.ViewModelProvider
import com.bumptech.glide.Glide
import com.google.android.material.bottomsheet.BottomSheetDialogFragment
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.google.android.material.snackbar.Snackbar
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.models.Me
import com.sipanduberadat.guest.services.FileDataPart
import com.sipanduberadat.guest.services.apis.validateEmergencyReportAPI
import com.sipanduberadat.guest.services.apis.validateNotEmergencyReportAPI
import com.sipanduberadat.guest.utils.choosePhoto
import com.sipanduberadat.guest.utils.snackbarWarning
import com.sipanduberadat.guest.viewModels.ReportDetailViewModel
import kotlinx.android.synthetic.main.activity_report_detail.*
import kotlinx.android.synthetic.main.bottom_sheet_report.*
import kotlinx.android.synthetic.main.bottom_sheet_report.view.*
import java.io.ByteArrayOutputStream
import java.util.*
import kotlin.collections.HashMap

class ReportBottomSheetDialog(
        private val me: Me,
        private val validStatus: Boolean,
        private val dismissCallback: () -> Unit
): BottomSheetDialogFragment() {
    private lateinit var viewModel: ReportDetailViewModel
    private var status: Boolean = false
    private var byteArray: ByteArray? = null

    private fun onSuccessValidate(response: Any?) {
        if (response == null) {
            status = true
            isCancelable = true
            dialog?.setCanceledOnTouchOutside(true)
            dismiss()
        }
    }

    private fun onRequestError() {
        isCancelable = true
        dialog?.setCanceledOnTouchOutside(true)
        btn_upload.stopProgress()
    }

    private fun onUpload() {
        if (byteArray == null) {
            btn_upload.stopProgress()
            snackbarWarning(view!!.rootView, "Mohon isi foto bukti penanganan terlebih dahulu",
                    Snackbar.LENGTH_LONG).show()
            return
        }

        isCancelable = false
        dialog?.setCanceledOnTouchOutside(false)

        val requestParams = HashMap<String, String>()
        requestParams["id_pecalang"] = "${me.pecalang!!.id}"
        if (viewModel.report.value!!.jenis_pelaporan.emergency_status)
            requestParams["id_pelaporan_darurat"] = "${viewModel.report.value!!.id}" else
                requestParams["id_pelaporan"] = "${viewModel.report.value!!.id}"
        requestParams["valid_status"] = "$validStatus"

        val fileRequestParams = HashMap<String, FileDataPart>()
        fileRequestParams["photo"] = FileDataPart(UUID.randomUUID().toString(),
                byteArray!!, "image/jpeg")


        if (viewModel.report.value!!.jenis_pelaporan.emergency_status)
            validateEmergencyReportAPI(activity!!.root, view!!.context, requestParams,
                    fileRequestParams, this::onSuccessValidate, this::onRequestError) else
                        validateNotEmergencyReportAPI(activity!!.root, view!!.context, requestParams,
                                fileRequestParams, this::onSuccessValidate, this::onRequestError)
    }

    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.bottom_sheet_report, container, false)
        viewModel = ViewModelProvider(activity!!).get(ReportDetailViewModel::class.java)

        view.empty_photo.setOnClickListener {
            val intentChooser = choosePhoto()
            startActivityForResult(intentChooser, 1)
        }

        view.photo.setOnClickListener {
            val intentChooser = choosePhoto()
            startActivityForResult(intentChooser, 1)
        }

        view.btn_upload.setOnClickListener {
            MaterialAlertDialogBuilder(view.context)
                    .setTitle("Validasi Laporan")
                    .setMessage("Apakah Anda yakin bahwa laporan ini ${if (validStatus) "valid" else "tidak valid"}?")
                    .setNegativeButton("Batal") { dialog, _ ->
                        view.btn_upload.stopProgress()
                        dialog.dismiss()
                    }
                    .setPositiveButton("Yakin") { dialog, _ ->
                        onUpload()
                        dialog.dismiss()
                    }.show()
        }

        return view
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        if (resultCode == Activity.RESULT_OK) {
            if (data != null) {
                if (requestCode == 1) {
                    if (data.data != null) {
                        val uri = data.data
                        byteArray = view!!.context.contentResolver.openInputStream(uri!!)?.buffered()?.use {
                            it.readBytes() }

                        Glide.with(view!!.context).load(uri).centerCrop().into(view!!.photo)
                        view!!.empty_photo.visibility = View.GONE
                        view!!.photo.visibility = View.VISIBLE
                        return
                    }

                    val bitmap = data.extras!!.get("data") as Bitmap
                    val stream = ByteArrayOutputStream()
                    bitmap.compress(Bitmap.CompressFormat.JPEG, 100, stream)
                    byteArray = stream.toByteArray()

                    Glide.with(view!!.context).load(bitmap).centerCrop().into(view!!.photo)
                    view!!.empty_photo.visibility = View.GONE
                    view!!.photo.visibility = View.VISIBLE
                }
            }
        }
    }

    override fun onDismiss(dialog: DialogInterface) {
        if (status) dismissCallback()
        super.onDismiss(dialog)
    }
}