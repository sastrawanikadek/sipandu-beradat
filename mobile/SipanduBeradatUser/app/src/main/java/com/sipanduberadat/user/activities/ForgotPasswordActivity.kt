package com.sipanduberadat.user.activities

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import com.sipanduberadat.user.R
import com.sipanduberadat.user.services.apis.forgotPasswordAPI
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
            new_password_input_layout.helperText = "Kata sandi baru tidak boleh kosong"
            new_password_edit_text.requestFocus()
            btn_save.stopProgress()
            return
        }

        if (confirmNewPassword.isEmpty()) {
            confirm_new_password_input_layout.helperText = "Konfirmasi kata sandi baru tidak boleh kosong"
            confirm_new_password_edit_text.requestFocus()
            btn_save.stopProgress()
            return
        }

        if (newPassword != confirmNewPassword) {
            confirm_new_password_input_layout.helperText = "Konfirmasi kata sandi baru tidak sama"
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