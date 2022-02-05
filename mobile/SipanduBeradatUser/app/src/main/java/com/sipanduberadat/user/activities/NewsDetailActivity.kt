package com.sipanduberadat.user.activities

import android.graphics.Color
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.webkit.WebSettings
import androidx.appcompat.app.AppCompatDelegate
import com.bumptech.glide.Glide
import com.sipanduberadat.user.R
import com.sipanduberadat.user.models.Berita
import com.sipanduberadat.user.utils.getDateTime
import kotlinx.android.synthetic.main.activity_news_detail.*

class NewsDetailActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_news_detail)

        val textColor = if (AppCompatDelegate.getDefaultNightMode() == AppCompatDelegate.MODE_NIGHT_NO)
            "black" else "white"
        val linkColor = if (AppCompatDelegate.getDefaultNightMode() == AppCompatDelegate.MODE_NIGHT_NO)
            "blue" else "white"
        val news: Berita = intent.getParcelableExtra<Berita>("NEWS") as Berita
        val locationText = "Desa Adat ${news.admin_desa.masyarakat.banjar.desa_adat.name}"
        val html = "<html>" +
                "<head>" +
                    "<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0'/>" +
                    "<style>" +
                        "body {" +
                            "overflow-wrap: break-word;" +
                            "word-break: break-word;" +
                            "line-height: 32px;" +
                            "background-color: transparent;" +
                            "color: $textColor;" +
                            "text-align: justify;" +
                        "}" +
                        "a { color: $linkColor; }" +
                    "</style>" +
                "</head>" +
                "<body>${news.content}</body>" +
            "</html>"

        time.text = getDateTime(news.time)
        title_text.text = news.title
        location.text = locationText
        Glide.with(this).load(news.cover).into(cover)
        Glide.with(this).load(news.admin_desa.masyarakat.avatar).centerCrop().into(avatar)
        author_name.text = news.admin_desa.masyarakat.name
        content.isVerticalScrollBarEnabled = false
        content.settings.layoutAlgorithm = WebSettings.LayoutAlgorithm.TEXT_AUTOSIZING
        content.loadData(html, "text/html", "utf-8")
        content.setBackgroundColor(Color.TRANSPARENT)

        btn_back.setOnClickListener { finish() }
    }
}