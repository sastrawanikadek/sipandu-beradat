package com.sipanduberadat.user.activities

import android.content.Intent
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.widget.ImageView
import android.widget.LinearLayout
import androidx.core.content.ContextCompat
import androidx.core.view.get
import androidx.viewpager.widget.ViewPager
import com.sipanduberadat.user.R
import com.sipanduberadat.user.adapters.FeatureViewPagerAdapter
import com.sipanduberadat.user.utils.toPx
import kotlinx.android.synthetic.main.activity_feature.*

class FeatureActivity : AppCompatActivity() {

    private val images: List<Int> = listOf(R.drawable.ic_laporan_darurat,
            R.drawable.ic_laporan_keluhan, R.drawable.ic_notif_kerabat)
    private val titles: List<String> = listOf("Laporan Darurat", "Laporan Keluhan",
            "Notifikasi Kerabat")
    private val descriptions: List<String> = listOf(
            "Keamanan terjamin, laporkan keadaan darurat Anda hanya dengan menekan 1 tombol darurat",
            "Ajukan laporan keluhan Anda agar pihak berwenang dapat menindaklanjutinya",
            "Dapatkan notifikasi pelaporan darurat yang dikirimkan oleh kerabat Anda"
    )

    private fun onChangeIndicator(initial: Boolean = false) {
        for (i in images.indices) {
            val imageView = if (initial) ImageView(this) else view_pager_dot_wrapper[i]
                    as ImageView
            val drawable = ContextCompat.getDrawable(this,
                    if (i == view_pager.currentItem) R.drawable.active_dot_indicator else
                        R.drawable.inactive_dot_indicator)
            val layoutParams: LinearLayout.LayoutParams = LinearLayout.LayoutParams(
                    if (i == view_pager.currentItem) 32.toPx() else 12.toPx(), 12.toPx()
            )
            layoutParams.setMargins(8, 0, 8, 0)

            imageView.apply {
                setImageDrawable(drawable)
                setLayoutParams(layoutParams)
            }

            if (initial) {
                view_pager_dot_wrapper.addView(imageView)
            }
        }
    }

    private fun onGoToLogin() {
        val intent = Intent(this, LoginActivity::class.java)
        startActivity(intent)
        finish()
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_feature)
        view_pager.adapter = FeatureViewPagerAdapter(this, images, titles, descriptions)
        view_pager.addOnPageChangeListener(object: ViewPager.OnPageChangeListener {
            override fun onPageScrollStateChanged(state: Int) {}

            override fun onPageScrolled(position: Int, positionOffset: Float, positionOffsetPixels: Int) {}

            override fun onPageSelected(position: Int) {
                btn_next.setText(if (view_pager.currentItem == images.size - 1) R.string.masuk else R.string.lanjut)
                onChangeIndicator()
            }
        })
        btn_next.setOnClickListener { if (view_pager.currentItem == images.size - 1) onGoToLogin() else
            view_pager.currentItem += 1 }
        btn_skip.setOnClickListener { onGoToLogin() }
        onChangeIndicator(initial = true)
    }
}