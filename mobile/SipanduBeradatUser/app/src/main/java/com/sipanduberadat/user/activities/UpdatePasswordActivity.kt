package com.sipanduberadat.user.activities

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import com.sipanduberadat.user.R
import com.sipanduberadat.user.services.apis.changePasswordAPI
import kotlinx.android.synthetic.main.activity_update_password.*

class UpdatePasswordActivity : AppCompatActivity() {
    private var code = ""

    private fun onSuccessSave(response: Any?) {
        if (response == null) {
            Handler(Looper.getMainLooper()).postDelayed({ finish() }, 1000)
        }
    }

    private fun onRequestError() { btn_save.stopProgress() }

    private fun onSave() {
        val oldPassword = "${old_password_edit_text.text}"
        val newPassword = "${new_password_edit_text.text}"
        val confirmNewPassword = "${confirm_new_password_edit_text.text}"

        old_password_input_layout.helperText = ""
        new_password_input_layout.helperText = ""
        confirm_new_password_input_layout.helperText = ""

        if (oldPassword.isEmpty()) {
            old_password_input_layout.helperText = "Kata sandi lama tidak boleh kosong"
            old_password_edit_text.requestFocus()
            btn_save.stopProgress()
            return
        }

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
        requestParams["old_password"] = oldPassword
        requestParams["new_password"] = newPassword
        changePasswordAPI(root, this, requestParams, HashMap(), this::onSuccessSave,
            this::onRequestError)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_update_password)

        code = intent.getStringExtra("CODE")!!

        btn_save.setOnClickListener { onSave() }
        btn_back.setOnClickListener { finish() }
    }
}