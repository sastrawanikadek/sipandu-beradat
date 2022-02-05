package com.sipanduberadat.user.adapters

import android.content.Context
import android.content.Intent
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.viewpager.widget.PagerAdapter
import com.google.android.material.card.MaterialCardView
import com.sipanduberadat.user.R
import com.sipanduberadat.user.activities.BlockedRoadDetailActivity
import com.sipanduberadat.user.activities.NewsDetailActivity
import com.sipanduberadat.user.models.BeritaWrapper
import kotlinx.android.synthetic.main.layout_item_news_headline_title.view.*

class NewsHeadlineTitleViewPagerAdapter(
        private val context: Context,
        private val news: List<BeritaWrapper>
): PagerAdapter() {
    override fun instantiateItem(container: ViewGroup, position: Int): Any {
        val view = LayoutInflater.from(context).inflate(R.layout.layout_item_news_headline_title,
                container, false)
        view.text.text = if (news[position].news != null) news[position].news!!.title else
            news[position].blockedRoad!!.title
        view.container.setOnClickListener {
            if (news[position].news != null) {
                val intent = Intent(context, NewsDetailActivity::class.java)
                intent.putExtra("NEWS", news[position].news!!)
                context.startActivity(intent)
            } else if (news[position].blockedRoad != null) {
                val intent = Intent(context, BlockedRoadDetailActivity::class.java)
                intent.putExtra("BLOCKED_ROAD", news[position].blockedRoad!!)
                context.startActivity(intent)
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