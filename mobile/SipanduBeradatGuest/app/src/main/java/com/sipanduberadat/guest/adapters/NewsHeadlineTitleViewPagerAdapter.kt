package com.sipanduberadat.guest.adapters

import android.content.Context
import android.content.Intent
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.viewpager.widget.PagerAdapter
import com.google.android.material.card.MaterialCardView
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.activities.BlockedRoadDetailActivity
import com.sipanduberadat.guest.activities.NewsDetailActivity
import com.sipanduberadat.guest.models.BeritaWrapper
import kotlinx.android.synthetic.main.layout_item_news_headline_title.view.*

class NewsHeadlineTitleViewPagerAdapter(
        private val context: Context,
        private val news: List<BeritaWrapper>
): PagerAdapter() {
    override fun instantiateItem(container: ViewGroup, position: Int): Any {
        val view = LayoutInflater.from(context).inflate(R.layout.layout_item_news_headline_title,
                container, false)
        view.text.text = when {
            news[position].news != null -> news[position].news!!.title
            news[position].accommodationNews != null -> news[position].accommodationNews!!.title
            else -> news[position].blockedRoad!!.title
        }
        view.container.setOnClickListener {
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
        return container.removeView(`object` as MaterialCardView)
    }
}