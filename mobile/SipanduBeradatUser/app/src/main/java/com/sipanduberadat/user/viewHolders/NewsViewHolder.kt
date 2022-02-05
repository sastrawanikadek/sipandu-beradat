package com.sipanduberadat.user.viewHolders

import android.content.Intent
import android.view.View
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.google.android.flexbox.FlexboxLayoutManager
import com.sipanduberadat.user.activities.NewsDetailActivity
import com.sipanduberadat.user.activities.ReportDetailActivity
import com.sipanduberadat.user.models.BeritaWrapper
import com.sipanduberadat.user.models.Me
import com.sipanduberadat.user.utils.getRelativeDateTimeString
import com.sipanduberadat.user.utils.toPx
import kotlinx.android.synthetic.main.layout_item_news.view.*

class NewsViewHolder(private val view: View): RecyclerView.ViewHolder(view) {
    fun onBindItem(me: Me, news: BeritaWrapper, isEven: Boolean) {
        val cover = when {
            news.news != null -> news.news!!.cover
            news.report != null -> if (news.report!!.jenis_pelaporan.emergency_status)
                news.report!!.pecalang_reports[0].photo else news.report!!.photo
            else -> if (news.guestReport!!.jenis_pelaporan.emergency_status)
                news.guestReport!!.pecalang_reports[0].photo else news.guestReport!!.photo
        }
        val title = when {
            news.news != null -> news.news!!.title
            news.report != null -> if (news.report!!.jenis_pelaporan.emergency_status)
                news.report!!.jenis_pelaporan.name else news.report!!.title
            else -> if (news.guestReport!!.jenis_pelaporan.emergency_status)
                news.guestReport!!.jenis_pelaporan.name else news.guestReport!!.title
        }
        val time = when {
            news.news != null -> getRelativeDateTimeString(news.news!!.time.time)
            news.report != null -> getRelativeDateTimeString(news.report!!.time.time)
            else -> getRelativeDateTimeString(news.guestReport!!.time.time)
        }
        val author = when {
            news.news != null -> news.news!!.admin_desa.masyarakat.name
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
            if (news.news != null) {
                val intent = Intent(view.context, NewsDetailActivity::class.java)
                intent.putExtra("NEWS", news.news!!)
                view.context.startActivity(intent)
            } else if (news.report != null) {
                val intent = Intent(view.context, ReportDetailActivity::class.java)
                intent.putExtra("REPORT_ID", news.report!!.id)
                intent.putExtra("REPORT_EMERGENCY_STATUS", news.report!!.jenis_pelaporan.emergency_status)
                intent.putExtra("IS_REPORTER_KRAMA", true)
                intent.putExtra("ME", me)
                view.context.startActivity(intent)
            } else if (news.guestReport != null) {
                val intent = Intent(view.context, ReportDetailActivity::class.java)
                intent.putExtra("REPORT_ID", news.guestReport!!.id)
                intent.putExtra("REPORT_EMERGENCY_STATUS", news.guestReport!!.jenis_pelaporan.emergency_status)
                intent.putExtra("IS_REPORTER_KRAMA", false)
                intent.putExtra("ME", me)
                view.context.startActivity(intent)
            }
        }
    }
}