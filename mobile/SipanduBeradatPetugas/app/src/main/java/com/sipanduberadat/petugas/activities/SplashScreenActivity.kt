package com.sipanduberadat.petugas.activities

import android.content.Context
import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.animation.AnimationUtils
import androidx.appcompat.app.AppCompatDelegate
import com.sipanduberadat.petugas.R
import kotlinx.android.synthetic.main.activity_splash_screen.*

class SplashScreenActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_splash_screen)

        val sharedPreferences = getSharedPreferences("PREFERENCES", Context.MODE_PRIVATE)

        if (!sharedPreferences.contains("DAY_NIGHT")) {
            val editor = sharedPreferences.edit()
            editor.putInt("DAY_NIGHT", AppCompatDelegate.MODE_NIGHT_NO)
            editor.apply()
        }

        AppCompatDelegate.setDefaultNightMode(sharedPreferences.getInt("DAY_NIGHT",
            AppCompatDelegate.MODE_NIGHT_FOLLOW_SYSTEM))

        val animation = AnimationUtils.loadAnimation(this, R.anim.slow_fade_in)
        logo.startAnimation(animation)

        Handler(Looper.getMainLooper()).postDelayed({
            val sessionSharedPreferences = getSharedPreferences("SESSIONS", Context.MODE_PRIVATE)

            val intent = Intent(this@SplashScreenActivity,
                if (sessionSharedPreferences.contains("ACCESS_TOKEN")) MainActivity::class.java
                else LoginActivity::class.java)
            startActivity(intent)
            finish()
        }, 1000)
    }

}