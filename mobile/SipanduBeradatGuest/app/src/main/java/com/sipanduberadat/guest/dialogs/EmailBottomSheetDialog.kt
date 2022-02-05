package com.sipanduberadat.guest.dialogs

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import com.google.android.material.bottomsheet.BottomSheetDialogFragment
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.activities.VerifyCodeActivity
import com.sipanduberadat.guest.services.apis.checkEmailAPI
import kotlinx.android.synthetic.main.bottom_sheet_email.view.*

class EmailBottomSheetDialog: BottomSheetDialogFragment() {
    private fun onSuccessCheck(response: Any?) {
        if (response != null) {
            val intent = Intent(view!!.context, VerifyCodeActivity::class.java)
            intent.putExtra("ID", "$response")
            intent.putExtra("ROLE", "tamu")
            intent.putExtra("ACTION", "forgot")
            startActivity(intent)
            dismiss()
        }
    }

    private fun onRequestError() {
        Toast.makeText(view!!.context, "Account has not been registered yet", Toast.LENGTH_SHORT).show()
        view!!.btn_forgot.stopProgress()
    }

    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.bottom_sheet_email, container, false)

        view.btn_forgot.setOnClickListener {
            val email = "${view.email_edit_text.text}"

            when {
                email.isBlank() -> {
                    view.btn_forgot.stopProgress()
                    view.email_input_layout.helperText = "Email cannot be empty"
                    view.email_edit_text.requestFocus()
                    return@setOnClickListener
                }
                !Regex("^[a-zA-Z0-9.!#\$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\\.[a-zA-Z0-9-]+)*\$")
                        .matches(email) -> {
                    view.btn_forgot.stopProgress()
                    view.email_input_layout.helperText = "Invalid email"
                    view.email_edit_text.requestFocus()
                    return@setOnClickListener
                }
                else -> {
                    view.email_input_layout.helperText = ""
                }
            }

            val requestParams = HashMap<String, String>()
            requestParams["email"] = email
            checkEmailAPI(view, view.context, requestParams, HashMap(), this::onSuccessCheck,
                    this::onRequestError, showMessage = false)
        }
        return view
    }
}