package com.sipanduberadat.guest.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.LinearLayout
import androidx.viewpager.widget.PagerAdapter
import com.bumptech.glide.Glide
import com.sipanduberadat.guest.R
import kotlinx.android.synthetic.main.layout_item_feature.view.*

class FeatureViewPagerAdapter(
        private val context: Context,
        private val images: List<Int>,
        private val titles: List<String>,
        private val descriptions: List<String>
): PagerAdapter() {
    override fun instantiateItem(container: ViewGroup, position: Int): Any {
        val view = LayoutInflater.from(context).inflate(R.layout.layout_item_feature, container,
                false)
        Glide.with(context).load(images[position]).fitCenter().into(view.image)
        view.title.text = titles[position]
        view.description.text = descriptions[position]
        container.addView(view)
        return view
    }

    override fun isViewFromObject(view: View, `object`: Any): Boolean {
        return view == `object`
    }

    override fun getCount(): Int {
        return images.size
    }

    override fun destroyItem(container: ViewGroup, position: Int, `object`: Any) {
        return container.removeView(`object` as LinearLayout)
    }
}