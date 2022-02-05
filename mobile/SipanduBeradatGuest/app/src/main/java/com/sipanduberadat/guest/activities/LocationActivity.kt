package com.sipanduberadat.guest.activities

import android.app.Activity
import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.models.Tamu
import kotlinx.android.synthetic.main.activity_location.*

class LocationActivity : AppCompatActivity() {
    private lateinit var me: Tamu

    private fun onBack() {
        val intent = Intent()
        intent.putExtra("ME", me)
        setResult(Activity.RESULT_OK, intent)
        finish()
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_location)

        me = intent.getParcelableExtra("ME")!!
        negara.text = me.negara.name
        accommodation.text = me.akomodasi.name
        btn_back.setOnClickListener { onBack() }
    }

    override fun onBackPressed() {
        onBack()
    }
}