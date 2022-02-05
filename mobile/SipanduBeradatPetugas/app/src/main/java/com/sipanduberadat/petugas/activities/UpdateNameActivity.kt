package com.sipanduberadat.petugas.activities

import android.app.Activity
import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.models.Me
import com.sipanduberadat.petugas.services.apis.changeNameAPI
import kotlinx.android.synthetic.main.activity_update_name.*

class UpdateNameActivity : AppCompatActivity() {
    private var resultCode: Int = Activity.RESULT_CANCELED

    private fun onSuccessChangeName(response: Any?) {
        if (response != null) {
            btn_save.stopProgress()
            resultCode = Activity.RESULT_OK
        }
    }

    private fun onRequestError() { btn_save.stopProgress() }

    private fun onBack() {
        val intent = Intent()
        intent.putExtra("NAME", "${name_edit_text.text}")
        setResult(resultCode, intent)
        finish()
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_update_name)

        val me: Me = intent.getParcelableExtra("ME")!!
        name_edit_text.setText(me.masyarakat.name)

        btn_back.setOnClickListener { onBack() }
        btn_save.setOnClickListener {
            val requestParams = HashMap<String, String>()
            requestParams["name"] = "${name_edit_text.text}"
            changeNameAPI(root, this, requestParams, HashMap(),
                this::onSuccessChangeName, this::onRequestError)
        }
    }

    override fun onBackPressed() {
        onBack()
    }
}