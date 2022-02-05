package com.sipanduberadat.guest.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.models.BeritaWrapper
import com.sipanduberadat.guest.viewHolders.NewsViewHolder

class NewsAdapter(
        private val context: Context,
        private val items: List<BeritaWrapper>
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
        holder.onBindItem(items[position], position % 2 == 0)
    }
}