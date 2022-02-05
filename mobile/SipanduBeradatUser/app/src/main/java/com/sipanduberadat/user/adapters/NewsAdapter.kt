package com.sipanduberadat.user.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.sipanduberadat.user.R
import com.sipanduberadat.user.models.BeritaWrapper
import com.sipanduberadat.user.models.Me
import com.sipanduberadat.user.viewHolders.NewsViewHolder

class NewsAdapter(
        private val context: Context,
        private val items: List<BeritaWrapper>,
        private val me: Me
): RecyclerView.Adapter<NewsViewHolder>() {
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): NewsViewHolder {
        val view = LayoutInflater.from(context).inflate(R.layout.layout_item_news, parent,
                false)
        return NewsViewHolder(view)
    }

    override fun getItemCount(): Int {
        return items.size
    }

    override fun onBindViewHolder(holder: NewsViewHolder, position: Int) {
        holder.onBindItem(me, items[position], position % 2 == 0)
    }
}