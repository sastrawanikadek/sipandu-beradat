package com.sipanduberadat.petugas.activities

import android.app.Activity
import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.models.Me
import com.sipanduberadat.petugas.services.apis.changeGenderAPI
import kotlinx.android.synthetic.main.activity_update_gender.*

class UpdateGenderActivity : AppCompatActivity() {
    private lateinit var me: Me
    private var resultCode: Int = Activity.RESULT_CANCELED

    private fun onSuccessChangeGender(response: Any?) {
        if (response != null) {
            btn_save.stopProgress()
            resultCode = Activity.RESULT_OK
        }
    }

    private fun onRequestError() { btn_save.stopProgress() }

    private fun onSelectGender(gender: String) {
        me.masyarakat.gender = gender

        if (gender == "l") {
            female_image_view.setImageResource(R.drawable.ic_female_grayscale)
            male_image_view.setImageResource(R.drawable.ic_male)
        } else {
            male_image_view.setImageResource(R.drawable.ic_male_grayscale)
            female_image_view.setImageResource(R.drawable.ic_female)
        }
    }

    private fun onBack() {
        val intent = Intent()
        intent.putExtra("GENDER", me.masyarakat.gender)
        setResult(resultCode, intent)
        finish()
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_update_gender)

        me = intent.getParcelableExtra("ME")!!
        onSelectGender(me.masyarakat.gender)

        male.setOnClickListener { onSelectGender("l") }
        female.setOnClickListener { onSelectGender("p") }

        btn_back.setOnClickListener { onBack() }
        btn_save.setOnClickListener {
            val requestParams = HashMap<String, String>()
            requestParams["gender"] = me.masyarakat.gender
            changeGenderAPI(root, this, requestParams, HashMap(),
                this::onSuccessChangeGender, this::onRequestError)
        }
    }

    override fun onBackPressed() {
        onBack()
    }
}