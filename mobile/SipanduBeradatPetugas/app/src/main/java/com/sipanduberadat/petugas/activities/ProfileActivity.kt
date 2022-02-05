package com.sipanduberadat.petugas.activities

import android.app.Activity
import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.models.Petugas
import com.sipanduberadat.petugas.utils.getDate
import kotlinx.android.synthetic.main.activity_profile.*

class ProfileActivity : AppCompatActivity() {
    private lateinit var me: Petugas

    private fun onBack() {
        val intent = Intent()
        intent.putExtra("ME", me)
        setResult(Activity.RESULT_OK, intent)
        finish()
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_profile)

        me = intent.getParcelableExtra("ME")!!
        name.text = me.name
        email.text = me.email
        username.text = me.username!!
        nik.text = me.nik
        gender.text = if (me.gender == "l") "Laki-laki" else "Perempuan"
        phone.text = me.phone
        dateOfBirth.text = getDate(me.date_of_birth)

        btn_back.setOnClickListener { onBack() }
    }

    override fun onBackPressed() {
        onBack()
    }
}