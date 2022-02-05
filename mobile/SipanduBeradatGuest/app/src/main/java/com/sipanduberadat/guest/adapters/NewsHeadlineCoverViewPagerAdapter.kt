package com.sipanduberadat.guest.adapters

import android.content.Context
import android.content.Intent
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import androidx.viewpager.widget.PagerAdapter
import com.bumptech.glide.Glide
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.activities.BlockedRoadDetailActivity
import com.sipanduberadat.guest.activities.NewsDetailActivity
import com.sipanduberadat.guest.models.BeritaWrapper
import kotlinx.android.synthetic.main.layout_item_news_headline_cover.view.*

class NewsHeadlineCoverViewPagerAdapter(
        private val context: Context,
        private val news: List<BeritaWrapper>
): PagerAdapter() {
    override fun instantiateItem(container: ViewGroup, position: Int): Any {
        val view = LayoutInflater.from(context).inflate(R.layout.layout_item_news_headline_cover,
                container, false)
        Glide.with(context).load(when {
            news[position].news != null -> news[position].news!!.cover
            news[position].accommodationNews != null -> news[position].accommodationNews!!.cover
            else -> news[position].blockedRoad!!.cover
        }).into(view.cover)
        view.cover.setOnClickListener {
            when {
                news[position].news != null -> {
                    val intent = Intent(context, NewsDetailActivity::class.java)
                    intent.putExtra("NEWS", news[position].news!!)
                    intent.putExtra("NEWS_SOURCE", "desa")
                    context.startActivity(intent)
                }
                news[position].accommodationNews != null -> {
                    val intent = Intent(context, NewsDetailActivity::class.java)
                    intent.putExtra("NEWS", news[position].accommodationNews!!)
                    intent.putExtra("NEWS_SOURCE", "accommodation")
                    context.startActivity(intent)
                }
                news[position].blockedRoad != null -> {
                    val intent = Intent(context, BlockedRoadDetailActivity::class.java)
                    intent.putExtra("BLOCKED_ROAD", news[position].blockedRoad!!)
                    context.startActivity(intent)
                }
            }
        }
        container.addView(view)
        return view
    }

    override fun getCount(): Int {
        return news.size
    }

    override fun isViewFromObject(view: View, `object`: Any): Boolean {
        return view == `object`
    }

    override fun destroyItem(container: ViewGroup, position: Int, `object`: Any) {
        return container.removeView(`object` as ImageView)
    }
}