package com.sipanduberadat.guest.activities

import android.Manifest
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Build
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import androidx.core.app.ActivityCompat
import com.google.firebase.messaging.FirebaseMessaging
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.dialogs.EmailBottomSheetDialog
import com.sipanduberadat.guest.models.Token
import com.sipanduberadat.guest.services.apis.fcmTokenAPI
import com.sipanduberadat.guest.services.apis.loginAPI
import com.sipanduberadat.guest.utils.checkPermissions
import kotlinx.android.synthetic.main.activity_login.*

class LoginActivity : AppCompatActivity() {
    private fun onSuccessFCMToken(response: Any?) {
        if (response == null) {
            val intent = Intent(this, MainActivity::class.java)
            startActivity(intent)
            finish()
        }
    }

    private fun onLoginSuccess(response: Any?) {
        if (response != null) {
            val token = response as Token
            val sharedPreferences = getSharedPreferences("SESSIONS", Context.MODE_PRIVATE)
            val editor = sharedPreferences.edit()
            editor.putString("ACCESS_TOKEN", token.access_token)
            editor.putString("REFRESH_TOKEN", token.refresh_token)
            editor.apply()

            FirebaseMessaging.getInstance().token.addOnSuccessListener {
                val requestParams: HashMap<String, String> = HashMap()
                requestParams["token"] = it

                fcmTokenAPI(root, this, requestParams, HashMap(),
                    this::onSuccessFCMToken, this::onRequestError, showMessage = false)
            }
        }
    }

    private fun onRequestError() { btn_login.stopProgress() }

    private fun onLogin() {
        val username = "${username_edit_text.text}"
        val password = "${password_edit_text.text}"
        val requestParams = HashMap<String, String>()
        requestParams["username"] = username
        requestParams["password"] = password

        loginAPI(root, this, requestParams, HashMap(),
                this::onLoginSuccess, this::onRequestError, showMessage = true)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        val permissions = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q)
            arrayOf(
                    Manifest.permission.READ_EXTERNAL_STORAGE,
                    Manifest.permission.ACCESS_FINE_LOCATION,
                    Manifest.permission.ACCESS_COARSE_LOCATION,
                    Manifest.permission.ACCESS_BACKGROUND_LOCATION
            )
        else
            arrayOf(
                    Manifest.permission.READ_EXTERNAL_STORAGE,
                    Manifest.permission.ACCESS_FINE_LOCATION,
                    Manifest.permission.ACCESS_COARSE_LOCATION
            )


        if (!checkPermissions(this, permissions)) {
            ActivityCompat.requestPermissions(this, permissions, 1)
        }

        btn_forgot.setOnClickListener { EmailBottomSheetDialog().show(supportFragmentManager,
                "EMAIL_BOTTOM_SHEET") }
        btn_login.setOnClickListener { onLogin() }
        btn_register.setOnClickListener {
            val intent = Intent(this, RegisterActivity::class.java)
            startActivity(intent)
        }
    }

    override fun onRequestPermissionsResult(
            requestCode: Int,
            permissions: Array<out String>,
            grantResults: IntArray
    ) {
        if (requestCode == 1) {
            for (grantResult in grantResults) {
                if (grantResult != PackageManager.PERMISSION_GRANTED) {
                    finish()
                }
            }
        }
    }
}