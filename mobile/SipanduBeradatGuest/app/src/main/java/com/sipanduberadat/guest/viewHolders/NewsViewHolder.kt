package com.sipanduberadat.guest.viewHolders

import android.content.Intent
import android.view.View
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.google.android.flexbox.FlexboxLayoutManager
import com.sipanduberadat.guest.activities.NewsDetailActivity
import com.sipanduberadat.guest.activities.ReportDetailActivity
import com.sipanduberadat.guest.models.BeritaWrapper
import com.sipanduberadat.guest.utils.getRelativeDateTimeString
import com.sipanduberadat.guest.utils.toPx
import kotlinx.android.synthetic.main.layout_item_news.view.*

class NewsViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    fun onBindItem(news: BeritaWrapper, isEven: Boolean) {
        val cover = when {
            news.blockedRoad != null -> news.blockedRoad!!.cover
            news.news != null -> news.news!!.cover
            news.accommodationNews != null -> news.accommodationNews!!.cover
            news.report != null && news.report!!.jenis_pelaporan.emergency_status -> news.report!!.pecalang_reports[0].photo
            news.report != null -> news.report!!.photo
            news.guestReport != null && news.guestReport!!.jenis_pelaporan.emergency_status ->
                news.guestReport!!.pecalang_reports[0].photo
            else -> news.guestReport!!.photo
        }
        val title = when {
            news.blockedRoad != null -> news.blockedRoad!!.title
            news.news != null -> news.news!!.title
            news.accommodationNews != null -> news.accommodationNews!!.title
            news.report != null && news.report!!.jenis_pelaporan.emergency_status -> news.report!!.jenis_pelaporan.name
            news.report != null -> news.report!!.title
            news.guestReport != null && news.guestReport!!.jenis_pelaporan.emergency_status ->
                news.guestReport!!.title
            else -> news.guestReport!!.title
        }
        val time = when {
            news.blockedRoad != null -> getRelativeDateTimeString(news.blockedRoad!!.start_time.time)
            news.news != null -> getRelativeDateTimeString(news.news!!.time.time)
            news.accommodationNews != null -> getRelativeDateTimeString(news.accommodationNews!!.time.time)
            news.report != null -> getRelativeDateTimeString(news.report!!.time.time)
            else -> getRelativeDateTimeString(news.guestReport!!.time.time)
        }
        val author = when {
            news.blockedRoad != null -> news.blockedRoad!!.pecalang.masyarakat.name
            news.news != null -> news.news!!.admin_desa.masyarakat.name
            news.accommodationNews != null -> news.accommodationNews!!.admin_akomodasi.pegawai.name
            news.report != null -> news.report!!.masyarakat.name
            else -> news.guestReport!!.tamu.name
        }
        val lp = view.container.layoutParams

        if (lp is FlexboxLayoutManager.LayoutParams) {
            lp.apply {
                flexBasisPercent = 0.475f
                setMargins(if (isEven) 0 else (8).toPx(), (8).toPx(), if (!isEven) 0 else (8).toPx(),
                        (8).toPx())
            }
        }

        Glide.with(view.context).load(cover).centerCrop().into(view.cover)
        view.title.text = title
        view.time.text = time
        view.author.text = author

        view.container.setOnClickListener {
            when {
                news.news != null -> {
                    val intent = Intent(view.context, NewsDetailActivity::class.java)
                    intent.putExtra("NEWS", news.news!!)
                    intent.putExtra("NEWS_SOURCE", "desa")
                    view.context.startActivity(intent)
                }
                news.accommodationNews != null -> {
                    val intent = Intent(view.context, NewsDetailActivity::class.java)
                    intent.putExtra("NEWS", news.accommodationNews!!)
                    intent.putExtra("NEWS_SOURCE", "accommodation")
                    view.context.startActivity(intent)
                }
                news.report != null -> {
                    val intent = Intent(view.context, ReportDetailActivity::class.java)
                    intent.putExtra("REPORT_ID", news.report!!.id)
                    intent.putExtra("REPORT_EMERGENCY_STATUS", news.report!!.jenis_pelaporan.emergency_status)
                    intent.putExtra("IS_REPORTER_KRAMA", true)
                    view.context.startActivity(intent)
                }
                else -> {
                    val intent = Intent(view.context, ReportDetailActivity::class.java)
                    intent.putExtra("REPORT_ID", news.guestReport!!.id)
                    intent.putExtra("REPORT_EMERGENCY_STATUS", news.guestReport!!.jenis_pelaporan.emergency_status)
                    intent.putExtra("IS_REPORTER_KRAMA", false)
                    view.context.startActivity(intent)
                }
            }
        }
    }
}