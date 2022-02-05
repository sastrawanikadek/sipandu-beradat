package com.sipanduberadat.guest.activities

import android.app.Activity
import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.models.Tamu
import com.sipanduberadat.guest.utils.getDate
import kotlinx.android.synthetic.main.activity_profile.*

class ProfileActivity : AppCompatActivity() {
    private lateinit var me: Tamu

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
        name.text = me.name
        email.text = me.email
        username.text = me.username!!
        identity_type.text = me.identity_type
        identity_number.text = me.identity_number
        gender.text = if (me.gender == "l") "Male" else "Female"
        phone.text = me.phone
        dateOfBirth.text = getDate(me.date_of_birth)

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
                    me.name = newName
                    name.text = newName
                } else {
                    val newGender: String = data.getStringExtra("GENDER")!!
                    me.gender = newGender
                    gender.text = if (me.gender == "l") "Male" else "Female"
                }
            }
        }
    }
}