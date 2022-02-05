package com.sipanduberadat.guest.dialogs

import android.content.DialogInterface
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import com.google.android.material.bottomsheet.BottomSheetDialogFragment
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.google.android.material.snackbar.Snackbar
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.models.Sirine
import com.sipanduberadat.guest.services.apis.ringSirineDesaAPI
import com.sipanduberadat.guest.utils.snackbarWarning
import kotlinx.android.synthetic.main.activity_siren.*
import kotlinx.android.synthetic.main.bottom_sheet_siren.view.*

class SirenBottomSheetDialog(
        private val siren: Sirine,
        private val reportTypeID: Long,
        private val dismissCallback: () -> Unit
): BottomSheetDialogFragment() {
    private var status: Boolean = false

    private fun onSuccessRing(response: Any?) {
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
        view!!.btn_turn_on.stopProgress()
    }

    private fun onTurnOnSiren() {
        val duration = "${view!!.duration_edit_text.text}"

        if (duration.isBlank()) {
            snackbarWarning(view!!.rootView, "Mohon isi durasi nyala E-Kulkul",
                    Snackbar.LENGTH_LONG).show()
            return
        }

        dialog!!.setCanceledOnTouchOutside(false)
        isCancelable = false

        val requestParams = HashMap<String, String>()
        requestParams["code"] = siren.code
        requestParams["id_jenis_pelaporan"] = "$reportTypeID"
        requestParams["duration"] = "${duration.toInt() * 60}"

        ringSirineDesaAPI(activity!!.root, view!!.context, requestParams, HashMap(), this::onSuccessRing,
                this::onRequestError)
    }

    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.bottom_sheet_siren, container, false)
        view.duration_edit_text.setOnFocusChangeListener { _, b ->
            view.duration_end_text_input_layout.setBoxStrokeColorStateList(
                    ContextCompat.getColorStateList(view.context,
                            if (b) R.color.focused_stroke_color else R.color.stroke_color)!!)
            view.duration_end_text_input_layout.setBoxStrokeWidthResource(if (b)
                R.dimen.focused_stroke_width else R.dimen.stroke_width)
        }
        view.btn_turn_on.setOnClickListener {
            MaterialAlertDialogBuilder(view.context)
                    .setTitle("Konfirmasi Penyalaan E-Kulkul")
                    .setMessage("Apakah Anda yakin ingin menyalakan E-Kulkul ini?")
                    .setNegativeButton("Batal") { dialog, _ ->
                        view.btn_turn_on.stopProgress()
                        dialog.dismiss()
                    }
                    .setPositiveButton("Yakin") { dialog, _ ->
                        onTurnOnSiren()
                        dialog.dismiss()
                    }.show()
        }
        return view
    }

    override fun onDismiss(dialog: DialogInterface) {
        if (status) dismissCallback()
        super.onDismiss(dialog)
    }
}