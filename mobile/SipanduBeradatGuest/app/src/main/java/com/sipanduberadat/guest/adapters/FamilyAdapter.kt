package com.sipanduberadat.guest.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.models.KerabatTamu
import com.sipanduberadat.guest.viewHolders.FamilyViewHolder

class FamilyAdapter(
        private val context: Context,
        private val items: MutableList<KerabatTamu>
): RecyclerView.Adapter<FamilyViewHolder>() {
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): FamilyViewHolder {
        val view = LayoutInflater.from(context).inflate(R.layout.layout_item_family, parent,
                false)
        return FamilyViewHolder(view)
    }

    override fun getItemCount(): Int {
        return items.size
    }

    override fun onBindViewHolder(holder: FamilyViewHolder, position: Int) {
        holder.onBindItem(items[position])
    }
}