package com.sipanduberadat.guest.activities

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.services.apis.forgotPasswordAPI
import kotlinx.android.synthetic.main.activity_forgot_password.*

class ForgotPasswordActivity : AppCompatActivity() {
    private var code = ""
    private var id = ""
    private var role = ""

    private fun onSuccessSave(response: Any?) {
        if (response == null) {
            Handler(Looper.getMainLooper()).postDelayed({ finish() }, 1000)
        }
    }

    private fun onRequestError() { btn_save.stopProgress() }

    private fun onSave() {
        val newPassword = "${new_password_edit_text.text}"
        val confirmNewPassword = "${confirm_new_password_edit_text.text}"

        new_password_input_layout.helperText = ""
        confirm_new_password_input_layout.helperText = ""

        if (newPassword.isEmpty()) {
            new_password_input_layout.helperText = "New password cannot be empty"
            new_password_edit_text.requestFocus()
            btn_save.stopProgress()
            return
        }

        if (confirmNewPassword.isEmpty()) {
            confirm_new_password_input_layout.helperText = "Confirm new password cannot be empty"
            confirm_new_password_edit_text.requestFocus()
            btn_save.stopProgress()
            return
        }

        if (newPassword != confirmNewPassword) {
            confirm_new_password_input_layout.helperText = "Confirm new password is not the same"
            confirm_new_password_edit_text.requestFocus()
            btn_save.stopProgress()
            return
        }

        val requestParams = HashMap<String, String>()
        requestParams["code"] = code
        requestParams["id"] = id
        requestParams["role"] = role
        requestParams["new_password"] = newPassword
        forgotPasswordAPI(root, this, requestParams, HashMap(), this::onSuccessSave,
                this::onRequestError)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_forgot_password)

        code = intent.getStringExtra("CODE")!!
        id = intent.getStringExtra("ID")!!
        role = intent.getStringExtra("ROLE")!!

        btn_save.setOnClickListener { onSave() }
        btn_back.setOnClickListener { finish() }
    }
}