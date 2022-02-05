package com.sipanduberadat.user.activities

import android.app.Activity
import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import com.sipanduberadat.user.R
import com.sipanduberadat.user.models.Me
import com.sipanduberadat.user.utils.getDate
import kotlinx.android.synthetic.main.activity_profile.*

class ProfileActivity : AppCompatActivity() {
    private lateinit var me: Me

    private fun onChangeActivity(cls: Class<*>, requestCode: Int) {
        val intent = Intent(this, cls)
        intent.putExtra("ME", me)
        startActivityForResult(intent, requestCode)
    }

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
        name.text = me.masyarakat.name
        username.text = me.masyarakat.username!!
        email.text = me.masyarakat.email
        nik.text = me.masyarakat.nik
        category.text = me.masyarakat.category
        gender.text = if (me.masyarakat.gender == "l") "Laki-laki" else "Perempuan"
        phone.text = me.masyarakat.phone
        dateOfBirth.text = getDate(me.masyarakat.date_of_birth)

        btn_back.setOnClickListener { onBack() }
        btn_change_name.setOnClickListener { onChangeActivity(UpdateNameActivity::class.java, 1) }
        btn_change_gender.setOnClickListener { onChangeActivity(UpdateGenderActivity::class.java, 2) }
    }

    override fun onBackPressed() {
        onBack()
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)

        if (resultCode == Activity.RESULT_OK) {
            if (data != null) {
                if (requestCode == 1) {
                    val newName: String = data.getStringExtra("NAME")!!
                    me.masyarakat.name = newName
                    name.text = newName
                } else {
                    val newGender: String = data.getStringExtra("GENDER")!!
                    me.masyarakat.gender = newGender
                    gender.text = if (me.masyarakat.gender == "l") "Laki-laki" else "Perempuan"
                }
            }
        }
    }
}