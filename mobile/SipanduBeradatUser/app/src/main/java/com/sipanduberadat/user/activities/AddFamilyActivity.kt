package com.sipanduberadat.user.activities

import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.view.inputmethod.EditorInfo
import com.sipanduberadat.user.R
import com.sipanduberadat.user.models.Me
import kotlinx.android.synthetic.main.activity_add_family.*

class AddFamilyActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_add_family)
        val me = intent.getParcelableExtra<Me>("ME") as Me
        username_edit_text.setOnEditorActionListener { _, i, _ ->
            if (i == EditorInfo.IME_ACTION_SEARCH) {
                val intent = Intent(this@AddFamilyActivity,
                    ProfileKramaActivity::class.java)
                intent.putExtra("USERNAME", "${username_edit_text.text}")
                intent.putExtra("ME", me)
                startActivity(intent)
                finish()
                return@setOnEditorActionListener true
            }
            false
        }

        btn_back.setOnClickListener { finish() }
    }
}